<?php

declare(strict_types=1);

namespace App\Infrastructure\Appointment;

use App\Entity\Candidate;
use App\Entity\CounselorAvailabilitySlot;
use App\Entity\User;
use App\Infrastructure\Candidate\CandidateTimelineService;
use App\Infrastructure\Mail\AppointmentBookedMailer;
use App\Repository\CounselorAvailabilitySlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AppointmentBookingService
{
    public function __construct(
        private CounselorAvailabilitySlotRepository $slotRepository,
        private EntityManagerInterface $entityManager,
        private AppointmentBookedMailer $mailer,
        private CandidateTimelineService $timelineService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSlot(CounselorAvailabilitySlot $slot, bool $includeCandidate = false): array
    {
        $data = [
            'id' => $slot->getId()->toRfc4122(),
            'startsAt' => $slot->getStartsAt()->format(\DateTimeInterface::ATOM),
            'endsAt' => $slot->getEndsAt()->format(\DateTimeInterface::ATOM),
            'status' => $slot->getStatus()->value,
            'subject' => $slot->getSubject(),
            'bookedAt' => $slot->getBookedAt()?->format(\DateTimeInterface::ATOM),
        ];

        if ($includeCandidate && null !== $slot->getCandidate()) {
            $candidate = $slot->getCandidate();
            $data['candidate'] = [
                'id' => $candidate->getId()->toRfc4122(),
                'firstName' => $candidate->getFirstName(),
                'lastName' => $candidate->getLastName(),
                'email' => $candidate->getEmail(),
                'referenceNumber' => $candidate->getReferenceNumber(),
            ];
        }

        return $data;
    }

    public function bookSlot(User $candidateUser, string $slotId, ?string $subject = null): CounselorAvailabilitySlot
    {
        $candidate = $this->resolveCandidateForUser($candidateUser);
        $counselor = $candidate->getAssignedCounselor();
        if (!$counselor instanceof User) {
            throw new BadRequestHttpException('Aucune conseillère assignée à votre dossier.');
        }

        if (!$counselor->isAppointmentCalendarEnabled()) {
            throw new BadRequestHttpException('La prise de rendez-vous est temporairement désactivée par votre conseillère.');
        }

        $slot = $this->slotRepository->find($slotId);
        if (!$slot instanceof CounselorAvailabilitySlot) {
            throw new NotFoundHttpException('Créneau introuvable.');
        }

        if ($slot->getCounselor() !== $counselor) {
            throw new AccessDeniedHttpException('Ce créneau ne correspond pas à votre conseillère.');
        }

        if (!$slot->isAvailable()) {
            throw new ConflictHttpException('Ce créneau n\'est plus disponible.');
        }

        if ($slot->getStartsAt() <= new \DateTimeImmutable()) {
            throw new BadRequestHttpException('Impossible de réserver un créneau passé.');
        }

        $slot->book($candidate, $subject ?: 'Entretien de suivi');
        $this->timelineService->record(
            $candidate,
            'appointment.booked',
            sprintf(
                'Rendez-vous réservé avec %s le %s',
                trim($counselor->getFirstName().' '.$counselor->getLastName()),
                $slot->getStartsAt()->format('d/m/Y H:i'),
            ),
            ['slotId' => $slot->getId()->toRfc4122()],
            $candidateUser,
        );

        $this->entityManager->flush();

        try {
            $this->mailer->notifyCounselor($counselor, $candidate, $slot);
        } catch (\Throwable) {
            // Ne pas bloquer la réservation si l'email échoue (ex. mailer null en dev)
        }

        return $slot;
    }

    public function resolveCandidateForUser(User $user): Candidate
    {
        if (!$user->hasRole('CANDIDATE')) {
            throw new AccessDeniedHttpException();
        }

        $candidate = $this->entityManager->getRepository(Candidate::class)->findOneBy([
            'email' => $user->getEmail(),
        ]);

        if (!$candidate instanceof Candidate) {
            throw new NotFoundHttpException('Dossier candidat introuvable.');
        }

        return $candidate;
    }
}

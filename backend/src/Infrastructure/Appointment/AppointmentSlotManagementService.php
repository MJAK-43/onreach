<?php

declare(strict_types=1);

namespace App\Infrastructure\Appointment;

use App\Entity\CounselorAvailabilitySlot;
use App\Entity\User;
use App\Repository\CounselorAvailabilitySlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AppointmentSlotManagementService
{
    private const SLOT_DURATION_MINUTES = 30;

    public function __construct(
        private CounselorAvailabilitySlotRepository $slotRepository,
        private EntityManagerInterface $entityManager,
        private AppointmentBookingService $bookingService,
    ) {
    }

    /**
     * @param list<string> $times HH:MM
     *
     * @return list<CounselorAvailabilitySlot>
     */
    public function createSlots(User $counselor, string $date, array $times): array
    {
        $this->assertCanManage($counselor);

        if ('' === trim($date)) {
            throw new BadRequestHttpException('date requise (Y-m-d).');
        }

        if ([] === $times) {
            throw new BadRequestHttpException('Au moins une heure requise.');
        }

        $created = [];
        foreach ($times as $time) {
            $created[] = $this->createSlot($counselor, $date, (string) $time, false);
        }

        $this->entityManager->flush();

        return $created;
    }

    public function createSlot(
        User $counselor,
        string $date,
        string $time,
        bool $flush = true,
    ): CounselorAvailabilitySlot {
        $this->assertCanManage($counselor);

        $startsAt = $this->parseSlotStart($date, $time);
        $endsAt = $startsAt->modify(sprintf('+%d minutes', self::SLOT_DURATION_MINUTES));

        $existing = $this->slotRepository->findOneByCounselorAndStart($counselor, $startsAt);
        if ($existing instanceof CounselorAvailabilitySlot) {
            if ($existing->isAvailable()) {
                return $existing;
            }

            throw new ConflictHttpException('Un créneau existe déjà à cette heure.');
        }

        $slot = new CounselorAvailabilitySlot($counselor, $startsAt, $endsAt);
        $this->slotRepository->save($slot, $flush);

        return $slot;
    }

    public function deleteAvailableSlot(User $counselor, string $slotId): void
    {
        $this->assertCanManage($counselor);

        $slot = $this->slotRepository->find($slotId);
        if (!$slot instanceof CounselorAvailabilitySlot) {
            throw new NotFoundHttpException('Créneau introuvable.');
        }

        if ($slot->getCounselor()->getId()->toRfc4122() !== $counselor->getId()->toRfc4122()) {
            throw new AccessDeniedHttpException('Ce créneau ne vous appartient pas.');
        }

        if (!$slot->isAvailable()) {
            throw new ConflictHttpException('Impossible de retirer un créneau déjà réservé.');
        }

        $this->entityManager->remove($slot);
        $this->entityManager->flush();
    }

    /**
     * @param list<string> $times HH:MM
     */
    public function closeAvailableSlots(User $counselor, string $date, array $times): int
    {
        $this->assertCanManage($counselor);

        if ('' === trim($date)) {
            throw new BadRequestHttpException('date requise (Y-m-d).');
        }

        $removed = 0;
        foreach ($times as $time) {
            if (!preg_match('/^\d{2}:\d{2}$/', (string) $time)) {
                continue;
            }

            $startsAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date.' '.$time);
            if (!$startsAt instanceof \DateTimeImmutable) {
                continue;
            }

            $slot = $this->slotRepository->findOneByCounselorAndStart($counselor, $startsAt);
            if (!$slot instanceof CounselorAvailabilitySlot || !$slot->isAvailable()) {
                continue;
            }

            $this->entityManager->remove($slot);
            ++$removed;
        }

        if ($removed > 0) {
            $this->entityManager->flush();
        }

        return $removed;
    }

    public function setCalendarEnabled(User $counselor, bool $enabled): User
    {
        $this->assertCanManage($counselor);
        $counselor->setAppointmentCalendarEnabled($enabled);
        $this->entityManager->flush();

        return $counselor;
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSlot(CounselorAvailabilitySlot $slot): array
    {
        return $this->bookingService->serializeSlot($slot, true);
    }

    private function assertCanManage(User $counselor): void
    {
        if (!$counselor->hasRole('COUNSELOR') && !$counselor->hasRole('ADMIN') && !$counselor->hasRole('SUPER_ADMIN')) {
            throw new AccessDeniedHttpException('Réservé aux conseillers.');
        }
    }

    private function parseSlotStart(string $date, string $time): \DateTimeImmutable
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new BadRequestHttpException('Format date invalide (Y-m-d attendu).');
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            throw new BadRequestHttpException('Format heure invalide (HH:MM attendu).');
        }

        $startsAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date.' '.$time);
        if (!$startsAt instanceof \DateTimeImmutable) {
            throw new BadRequestHttpException('Date ou heure invalide.');
        }

        if ($startsAt <= new \DateTimeImmutable()) {
            throw new BadRequestHttpException('Impossible de créer un créneau passé.');
        }

        return $startsAt;
    }
}

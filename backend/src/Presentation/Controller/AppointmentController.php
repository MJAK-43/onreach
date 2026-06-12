<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\CounselorAvailabilitySlot;
use App\Entity\User;
use App\Infrastructure\Appointment\AppointmentBookingService;
use App\Infrastructure\Appointment\AppointmentSlotManagementService;
use App\Repository\CounselorAvailabilitySlotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/appointments')]
final class AppointmentController extends AbstractController
{
    public function __construct(
        private readonly CounselorAvailabilitySlotRepository $slotRepository,
        private readonly AppointmentBookingService $bookingService,
        private readonly AppointmentSlotManagementService $slotManagementService,
    ) {
    }

    #[Route('/available', name: 'appointments_available', methods: ['GET'])]
    #[IsGranted('appointments.view')]
    public function available(Request $request): JsonResponse
    {
        $user = $this->requireUser();
        $candidate = $this->bookingService->resolveCandidateForUser($user);
        $counselor = $candidate->getAssignedCounselor();
        if (!$counselor instanceof User) {
            return new JsonResponse(['counselor' => null, 'slots' => [], 'calendarEnabled' => false]);
        }

        if (!$counselor->isAppointmentCalendarEnabled()) {
            return new JsonResponse([
                'counselor' => [
                    'id' => $counselor->getId()->toRfc4122(),
                    'firstName' => $counselor->getFirstName(),
                    'lastName' => $counselor->getLastName(),
                    'email' => $counselor->getEmail(),
                ],
                'slots' => [],
                'calendarEnabled' => false,
            ]);
        }

        [$from, $to] = $this->parseRange($request);

        $slots = $this->slotRepository->findAvailableForCounselor($counselor, $from, $to);

        return new JsonResponse([
            'counselor' => [
                'id' => $counselor->getId()->toRfc4122(),
                'firstName' => $counselor->getFirstName(),
                'lastName' => $counselor->getLastName(),
                'email' => $counselor->getEmail(),
            ],
            'slots' => array_map(
                fn (CounselorAvailabilitySlot $slot) => $this->bookingService->serializeSlot($slot),
                $slots,
            ),
            'calendarEnabled' => true,
        ]);
    }

    #[Route('/mine', name: 'appointments_mine', methods: ['GET'])]
    #[IsGranted('appointments.view')]
    public function mine(): JsonResponse
    {
        $user = $this->requireUser();
        $candidate = $this->bookingService->resolveCandidateForUser($user);
        $slots = $this->slotRepository->findBookedForCandidate($candidate);

        return new JsonResponse(array_map(
            fn (CounselorAvailabilitySlot $slot) => $this->bookingService->serializeSlot($slot, true),
            $slots,
        ));
    }

    #[Route('/book', name: 'appointments_book', methods: ['POST'])]
    #[IsGranted('appointments.book')]
    public function book(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $slotId = (string) ($data['slotId'] ?? '');
        if ('' === $slotId) {
            throw new BadRequestHttpException('slotId requis.');
        }

        $subject = isset($data['subject']) ? (string) $data['subject'] : null;
        $slot = $this->bookingService->bookSlot($this->requireUser(), $slotId, $subject);

        return new JsonResponse(
            $this->bookingService->serializeSlot($slot, true),
            Response::HTTP_CREATED,
        );
    }

    #[Route('/counselor', name: 'appointments_counselor', methods: ['GET'])]
    #[IsGranted('appointments.manage')]
    public function counselorSchedule(Request $request): JsonResponse
    {
        $user = $this->requireCounselorUser();
        [$from, $to] = $this->parseRange($request);
        $slots = $this->slotRepository->findForCounselorBetween($user, $from, $to);

        return new JsonResponse(array_map(
            fn (CounselorAvailabilitySlot $slot) => $this->bookingService->serializeSlot($slot, true),
            $slots,
        ));
    }

    #[Route('/slots', name: 'appointments_slots_create', methods: ['POST'])]
    #[IsGranted('appointments.manage')]
    public function createSlots(Request $request): JsonResponse
    {
        $user = $this->requireCounselorUser();
        $data = json_decode($request->getContent(), true) ?? [];
        $date = (string) ($data['date'] ?? '');

        $times = [];
        if (isset($data['times']) && is_array($data['times'])) {
            $times = array_map(strval(...), $data['times']);
        } elseif (isset($data['time'])) {
            $times = [(string) $data['time']];
        }

        if ([] === $times) {
            throw new BadRequestHttpException('time ou times requis.');
        }

        $slots = $this->slotManagementService->createSlots($user, $date, $times);

        return new JsonResponse(
            array_map(
                fn (CounselorAvailabilitySlot $slot) => $this->slotManagementService->serializeSlot($slot),
                $slots,
            ),
            Response::HTTP_CREATED,
        );
    }

    #[Route('/slots/close', name: 'appointments_slots_close', methods: ['POST'])]
    #[IsGranted('appointments.manage')]
    public function closeSlots(Request $request): JsonResponse
    {
        $user = $this->requireCounselorUser();
        $data = json_decode($request->getContent(), true) ?? [];
        $date = (string) ($data['date'] ?? '');
        $times = isset($data['times']) && is_array($data['times'])
            ? array_map(strval(...), $data['times'])
            : [];

        if ([] === $times) {
            throw new BadRequestHttpException('times requis.');
        }

        $removed = $this->slotManagementService->closeAvailableSlots($user, $date, $times);

        return new JsonResponse(['removed' => $removed]);
    }

    #[Route('/slots/{id}', name: 'appointments_slots_delete', methods: ['DELETE'])]
    #[IsGranted('appointments.manage')]
    public function deleteSlot(string $id): Response
    {
        $user = $this->requireCounselorUser();
        $this->slotManagementService->deleteAvailableSlot($user, $id);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/calendar', name: 'appointments_calendar_settings', methods: ['GET', 'PATCH'])]
    #[IsGranted('appointments.manage')]
    public function calendarSettings(Request $request): JsonResponse
    {
        $user = $this->requireCounselorUser();

        if ($request->isMethod('GET')) {
            return new JsonResponse(['enabled' => $user->isAppointmentCalendarEnabled()]);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $enabled = (bool) ($data['enabled'] ?? false);
        $this->slotManagementService->setCalendarEnabled($user, $enabled);

        return new JsonResponse(['enabled' => $user->isAppointmentCalendarEnabled()]);
    }

    private function requireCounselorUser(): User
    {
        $user = $this->requireUser();
        if (!$user->hasRole('COUNSELOR') && !$user->hasRole('ADMIN') && !$user->hasRole('SUPER_ADMIN')) {
            throw new BadRequestHttpException('Réservé aux conseillers.');
        }

        return $user;
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new BadRequestHttpException('Authentification requise.');
        }

        return $user;
    }

    /**
     * @return array{\DateTimeImmutable, \DateTimeImmutable}
     */
    private function parseRange(Request $request): array
    {
        $fromParam = $request->query->get('from');
        $toParam = $request->query->get('to');

        $from = is_string($fromParam) && '' !== $fromParam
            ? new \DateTimeImmutable($fromParam)
            : new \DateTimeImmutable('today');

        $to = is_string($toParam) && '' !== $toParam
            ? (new \DateTimeImmutable($toParam))->modify('+1 day')
            : $from->modify('+21 days');

        return [$from, $to];
    }
}

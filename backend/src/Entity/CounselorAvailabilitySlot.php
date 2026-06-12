<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Appointment\Enum\AppointmentSlotStatus;
use App\Repository\CounselorAvailabilitySlotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CounselorAvailabilitySlotRepository::class)]
#[ORM\Table(name: 'counselor_availability_slots')]
#[ORM\UniqueConstraint(name: 'uniq_counselor_slot_start', columns: ['counselor_id', 'starts_at'])]
class CounselorAvailabilitySlot
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $counselor;

    #[ORM\Column(name: 'starts_at')]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column(name: 'ends_at')]
    private \DateTimeImmutable $endsAt;

    #[ORM\Column(enumType: AppointmentSlotStatus::class)]
    private AppointmentSlotStatus $status;

    #[ORM\ManyToOne(targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Candidate $candidate = null;

    #[ORM\Column(name: 'booked_at', nullable: true)]
    private ?\DateTimeImmutable $bookedAt = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $subject = null;

    public function __construct(User $counselor, \DateTimeImmutable $startsAt, \DateTimeImmutable $endsAt)
    {
        $this->id = Uuid::v7();
        $this->counselor = $counselor;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->status = AppointmentSlotStatus::AVAILABLE;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCounselor(): User
    {
        return $this->counselor;
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getStatus(): AppointmentSlotStatus
    {
        return $this->status;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function getBookedAt(): ?\DateTimeImmutable
    {
        return $this->bookedAt;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function book(Candidate $candidate, ?string $subject = null): void
    {
        $this->candidate = $candidate;
        $this->subject = $subject;
        $this->status = AppointmentSlotStatus::BOOKED;
        $this->bookedAt = new \DateTimeImmutable();
    }

    public function isAvailable(): bool
    {
        return AppointmentSlotStatus::AVAILABLE === $this->status;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Entity\Candidate;
use App\Entity\CounselorAvailabilitySlot;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class AppointmentBookedMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromAddress,
    ) {
    }

    public function notifyCounselor(
        User $counselor,
        Candidate $candidate,
        CounselorAvailabilitySlot $slot,
    ): void {
        $startsAt = $slot->getStartsAt()->format('d/m/Y H:i');
        $endsAt = $slot->getEndsAt()->format('H:i');
        $candidateName = trim($candidate->getFirstName().' '.$candidate->getLastName());
        $subjectLine = $slot->getSubject() ?? 'Rendez-vous candidat';

        $email = (new Email())
            ->from($this->fromAddress)
            ->to($counselor->getEmail())
            ->subject("On'Reach — Nouveau rendez-vous avec {$candidateName}")
            ->text($this->buildTextBody($counselor, $candidateName, $candidate, $startsAt, $endsAt, $subjectLine));

        $this->mailer->send($email);
    }

    private function buildTextBody(
        User $counselor,
        string $candidateName,
        Candidate $candidate,
        string $startsAt,
        string $endsAt,
        string $subjectLine,
    ): string {
        return <<<TEXT
        Bonjour {$counselor->getFirstName()},

        {$candidateName} vient de réserver un créneau avec vous.

        Date : {$startsAt} — {$endsAt}
        Objet : {$subjectLine}
        Dossier : {$candidate->getReferenceNumber()}
        Email candidat : {$candidate->getEmail()}

        Connectez-vous à On'Reach pour consulter votre planning.

        — L'équipe On'Reach
        TEXT;
    }
}

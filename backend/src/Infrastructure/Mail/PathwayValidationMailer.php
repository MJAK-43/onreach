<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Entity\Candidate;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class PathwayValidationMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromAddress,
        private string $frontendUrl,
    ) {
    }

    public function notifyCandidateStepValidated(
        Candidate $candidate,
        string $pathwayName,
        string $subStepTitle,
    ): void {
        $candidateName = trim($candidate->getFirstName().' '.$candidate->getLastName());

        $email = (new Email())
            ->from($this->fromAddress)
            ->to($candidate->getEmail())
            ->subject("On'Reach — Étape validée : {$subStepTitle}")
            ->text($this->buildCandidateValidatedBody($candidateName, $pathwayName, $subStepTitle));

        $this->mailer->send($email);
    }

    public function notifyAdminValidationRequired(
        User $admin,
        Candidate $candidate,
        string $pathwayName,
        string $subStepTitle,
    ): void {
        $candidateName = trim($candidate->getFirstName().' '.$candidate->getLastName());

        $email = (new Email())
            ->from($this->fromAddress)
            ->to($admin->getEmail())
            ->subject("On'Reach — Validation admin requise ({$candidateName})")
            ->text($this->buildAdminRequiredBody($admin, $candidateName, $candidate, $pathwayName, $subStepTitle));

        $this->mailer->send($email);
    }

    private function buildCandidateValidatedBody(
        string $candidateName,
        string $pathwayName,
        string $subStepTitle,
    ): string {
        return <<<TEXT
        Bonjour {$candidateName},

        Votre conseiller a validé l'étape « {$subStepTitle} » du parcours {$pathwayName}.

        Consultez votre progression sur On'Reach :
        {$this->frontendUrl}/demarches

        — L'équipe On'Reach
        TEXT;
    }

    private function buildAdminRequiredBody(
        User $admin,
        string $candidateName,
        Candidate $candidate,
        string $pathwayName,
        string $subStepTitle,
    ): string {
        return <<<TEXT
        Bonjour {$admin->getFirstName()},

        Une validation administrateur est requise pour le candidat {$candidateName}.

        Parcours : {$pathwayName}
        Étape : {$subStepTitle}
        Dossier : {$candidate->getReferenceNumber()}

        Connectez-vous à On'Reach pour valider :
        {$this->frontendUrl}/candidates

        — L'équipe On'Reach
        TEXT;
    }
}

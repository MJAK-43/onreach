<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class PasswordResetMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private string $frontendUrl,
        private string $fromAddress,
    ) {
    }

    public function send(User $user, string $plainToken): void
    {
        $resetUrl = rtrim($this->frontendUrl, '/').'/reset-password?token='.urlencode($plainToken);

        $email = (new Email())
            ->from($this->fromAddress)
            ->to($user->getEmail())
            ->subject("On'Reach — Réinitialisation de votre mot de passe")
            ->text($this->buildTextBody($user, $resetUrl));

        $this->mailer->send($email);
    }

    private function buildTextBody(User $user, string $resetUrl): string
    {
        return <<<TEXT
        Bonjour {$user->getFirstName()},

        Vous avez demandé la réinitialisation de votre mot de passe On'Reach.

        Cliquez sur le lien suivant (valable 1 heure) :
        {$resetUrl}

        Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.

        — L'équipe On'Reach
        TEXT;
    }
}

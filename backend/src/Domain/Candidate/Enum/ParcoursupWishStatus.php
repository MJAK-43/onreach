<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum ParcoursupWishStatus: string
{
    case BROUILLON = 'brouillon';
    case SOUMIS = 'soumis';
    case EN_ANALYSE = 'en_analyse';
    case ACCEPTE = 'accepte';
    case LISTE_ATTENTE = 'liste_attente';
    case REFUSE = 'refuse';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::SOUMIS => 'Soumis',
            self::EN_ANALYSE => 'En analyse',
            self::ACCEPTE => 'Accepté',
            self::LISTE_ATTENTE => 'Liste d\'attente',
            self::REFUSE => 'Refusé',
        };
    }
}

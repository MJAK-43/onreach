<?php

declare(strict_types=1);

namespace App\Application\Auth\Query;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class GetCurrentUserQueryHandler
{
    public function __construct(private Security $security)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(GetCurrentUserQuery $query): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('Bearer', 'Non authentifié.');
        }

        return [
            'id' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'roles' => array_map(fn ($r) => str_replace('ROLE_', '', $r), $user->getRoles()),
            'permissions' => $user->getPermissions(),
            'mfaEnabled' => $user->isMfaEnabled(),
            'isActive' => $user->isActive(),
            'appointmentCalendarEnabled' => $user->isAppointmentCalendarEnabled(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}

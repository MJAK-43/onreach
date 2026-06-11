<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use App\Infrastructure\Security\AuditTrailService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Uid\Uuid;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final readonly class AuditTrailDoctrineListener
{
    public function __construct(
        private AuditTrailService $auditTrailService,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->isAuditable($entity)) {
            return;
        }

        $this->auditTrailService->record(
            'created',
            $this->entityType($entity),
            $this->entityId($entity),
            null,
            $this->snapshot($entity),
        );
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->isAuditable($entity)) {
            return;
        }

        $changeSet = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($entity);
        if ([] === $changeSet) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($changeSet as $field => $values) {
            if (!is_array($values)) {
                continue;
            }
            $old[$field] = $this->normalizeValue($values[0] ?? null);
            $new[$field] = $this->normalizeValue($values[1] ?? null);
        }

        $this->auditTrailService->record(
            'updated',
            $this->entityType($entity),
            $this->entityId($entity),
            $old,
            $new,
        );
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$this->isAuditable($entity)) {
            return;
        }

        $this->auditTrailService->record(
            'deleted',
            $this->entityType($entity),
            $this->entityId($entity),
            $this->snapshot($entity),
            null,
        );
    }

    private function isAuditable(object $entity): bool
    {
        return $entity instanceof User || $entity instanceof Role || $entity instanceof Permission;
    }

    private function entityType(object $entity): string
    {
        return match (true) {
            $entity instanceof User => 'User',
            $entity instanceof Role => 'Role',
            $entity instanceof Permission => 'Permission',
            default => $entity::class,
        };
    }

    private function entityId(object $entity): ?string
    {
        if ($entity instanceof User || $entity instanceof Role || $entity instanceof Permission) {
            return $entity->getId()->toRfc4122();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(object $entity): array
    {
        if ($entity instanceof User) {
            return [
                'email' => $entity->getEmail(),
                'firstName' => $entity->getFirstName(),
                'lastName' => $entity->getLastName(),
                'isActive' => $entity->isActive(),
                'mfaEnabled' => $entity->isMfaEnabled(),
            ];
        }

        if ($entity instanceof Role) {
            return [
                'code' => $entity->getCode(),
                'name' => $entity->getName(),
                'isSystem' => $entity->isSystem(),
                'permissions' => $entity->getPermissions()->map(
                    fn (Permission $p) => $p->getCode(),
                )->toArray(),
            ];
        }

        if ($entity instanceof Permission) {
            return [
                'code' => $entity->getCode(),
                'name' => $entity->getName(),
                'isSystem' => $entity->isSystem(),
            ];
        }

        return [];
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Uuid) {
            return $value->toRfc4122();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if ($value instanceof PersistentCollection) {
            return array_values($value->map(function (object $item): string {
                if ($item instanceof User || $item instanceof Role || $item instanceof Permission) {
                    return $item->getCode();
                }

                return method_exists($item, 'getId') ? (string) $item->getId() : $item::class;
            })->toArray());
        }

        if (is_object($value) && method_exists($value, 'getCode')) {
            return $value->getCode();
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Permission>
 */
final class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    public function findByCode(string $code): ?Permission
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function save(Permission $permission, bool $flush = true): void
    {
        $this->getEntityManager()->persist($permission);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

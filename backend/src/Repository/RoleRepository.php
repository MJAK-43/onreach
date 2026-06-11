<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
final class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findByCode(string $code): ?Role
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function save(Role $role, bool $flush = true): void
    {
        $this->getEntityManager()->persist($role);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

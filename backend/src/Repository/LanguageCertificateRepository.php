<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LanguageCertificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LanguageCertificate>
 */
final class LanguageCertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LanguageCertificate::class);
    }
}

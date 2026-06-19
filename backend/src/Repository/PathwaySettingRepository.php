<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Entity\PathwaySetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PathwaySetting> */
final class PathwaySettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PathwaySetting::class);
    }

    /**
     * @return array<string, PathwaySetting>
     */
    public function findAllIndexedByCode(): array
    {
        $indexed = [];
        foreach ($this->findAll() as $setting) {
            $indexed[$setting->getPathwayCode()->value] = $setting;
        }

        return $indexed;
    }

    public function findOneByPathwayCode(PathwayCode $code): ?PathwaySetting
    {
        return $this->findOneBy(['pathwayCode' => $code]);
    }

    /**
     * @return list<PathwaySetting>
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.pathwayCode', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

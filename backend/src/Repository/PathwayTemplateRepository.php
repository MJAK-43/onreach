<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Entity\Campaign;
use App\Entity\PathwayTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PathwayTemplate> */
final class PathwayTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PathwayTemplate::class);
    }

    public function findOneByCampaignAndCode(Campaign $campaign, PathwayCode $code): ?PathwayTemplate
    {
        foreach ($this->findBy(['campaign' => $campaign]) as $template) {
            if ($template->getCode() === $code) {
                return $template;
            }
        }

        return null;
    }
}

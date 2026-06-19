<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Repository\PathwaySettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PathwaySettingRepository::class)]
#[ORM\Table(name: 'pathway_settings')]
#[ORM\UniqueConstraint(name: 'uniq_pathway_setting_code', columns: ['pathway_code'])]
class PathwaySetting
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(enumType: PathwayCode::class)]
    private PathwayCode $pathwayCode;

    #[ORM\Column]
    private bool $doubleValidationEnabled = false;

    public function __construct(PathwayCode $pathwayCode, bool $doubleValidationEnabled = false)
    {
        $this->id = Uuid::v7();
        $this->pathwayCode = $pathwayCode;
        $this->doubleValidationEnabled = $doubleValidationEnabled;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPathwayCode(): PathwayCode
    {
        return $this->pathwayCode;
    }

    public function isDoubleValidationEnabled(): bool
    {
        return $this->doubleValidationEnabled;
    }

    public function setDoubleValidationEnabled(bool $doubleValidationEnabled): self
    {
        $this->doubleValidationEnabled = $doubleValidationEnabled;

        return $this;
    }
}

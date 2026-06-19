<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Infrastructure\ApiPlatform\UserProcessor;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('users.view')"),
        new Get(security: "is_granted('users.view') or object == user"),
        new Post(security: "is_granted('users.create')"),
        new Patch(security: "is_granted('users.edit') or object == user"),
        new Delete(security: "is_granted('users.delete')"),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    processor: UserProcessor::class,
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['user:read'])]
    private Uuid $id;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write', 'candidate:read', 'candidate:list'])]
    private string $email;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:write', 'candidate:read', 'candidate:list'])]
    private string $firstName;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:write', 'candidate:read', 'candidate:list'])]
    private string $lastName;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    private bool $isActive = true;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private bool $mfaEnabled = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $appointmentCalendarEnabled = true;

    #[ORM\Column(nullable: true)]
    private ?string $mfaSecret = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lockedUntil = null;

    #[ORM\Column]
    private int $failedLoginAttempts = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $passwordChangedAt = null;

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_roles')]
    #[Groups(['user:read', 'user:write'])]
    private Collection $roles;

    public function __construct(string $email, string $firstName, string $lastName)
    {
        $this->id = Uuid::v7();
        $this->email = strtolower($email);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = '';
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->roles = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);
        $this->touch();

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        $this->passwordChangedAt = new \DateTimeImmutable();
        $this->touch();

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    /** @return list<string> */
    #[Ignore]
    public function getRoles(): array
    {
        $codes = $this->roles->map(fn (Role $role) => 'ROLE_'.$role->getCode())->toArray();

        return array_values(array_unique($codes));
    }

    /** @return list<string> */
    #[Groups(['user:read'])]
    public function getPermissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[$permission->getCode()] = true;
            }
        }

        return array_keys($permissions);
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        $this->touch();

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        $this->touch();

        return $this;
    }

    #[Groups(['user:read'])]
    public function getFullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->touch();

        return $this;
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }

    public function setMfaEnabled(bool $mfaEnabled): self
    {
        $this->mfaEnabled = $mfaEnabled;
        $this->touch();

        return $this;
    }

    public function isAppointmentCalendarEnabled(): bool
    {
        return $this->appointmentCalendarEnabled;
    }

    public function setAppointmentCalendarEnabled(bool $appointmentCalendarEnabled): self
    {
        $this->appointmentCalendarEnabled = $appointmentCalendarEnabled;
        $this->touch();

        return $this;
    }

    public function getMfaSecret(): ?string
    {
        return $this->mfaSecret;
    }

    public function setMfaSecret(?string $mfaSecret): self
    {
        $this->mfaSecret = $mfaSecret;
        $this->touch();

        return $this;
    }

    public function getLockedUntil(): ?\DateTimeImmutable
    {
        return $this->lockedUntil;
    }

    public function setLockedUntil(?\DateTimeImmutable $lockedUntil): self
    {
        $this->lockedUntil = $lockedUntil;

        return $this;
    }

    public function isLocked(): bool
    {
        return null !== $this->lockedUntil && $this->lockedUntil > new \DateTimeImmutable();
    }

    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function incrementFailedLoginAttempts(): self
    {
        ++$this->failedLoginAttempts;

        return $this;
    }

    public function resetFailedLoginAttempts(): self
    {
        $this->failedLoginAttempts = 0;
        $this->lockedUntil = null;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPasswordChangedAt(): ?\DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    /** @return Collection<int, Role> */
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $this->touch();
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $this->touch();
        }

        return $this;
    }

    public function hasRole(string $roleCode): bool
    {
        foreach ($this->roles as $role) {
            if ($role->getCode() === $roleCode) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permissionCode): bool
    {
        return in_array($permissionCode, $this->getPermissions(), true);
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}

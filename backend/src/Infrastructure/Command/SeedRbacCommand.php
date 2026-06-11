<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\User\Enum\SystemPermission;
use App\Domain\User\Enum\SystemRole;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed:rbac', description: 'Seed roles, permissions and default super admin')]
final class SeedRbacCommand extends Command
{
    public function __construct(
        private PermissionRepository $permissionRepository,
        private RoleRepository $roleRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $permissions = [];
        foreach (SystemPermission::cases() as $systemPermission) {
            $permission = $this->permissionRepository->findByCode($systemPermission->value);
            if (!$permission) {
                $permission = new Permission(
                    $systemPermission->value,
                    $systemPermission->label(),
                    true,
                );
                $permission->setDescription('Permission système');
                $this->permissionRepository->save($permission, false);
            }
            $permissions[$systemPermission->value] = $permission;
        }

        foreach (SystemRole::cases() as $systemRole) {
            $role = $this->roleRepository->findByCode($systemRole->value);
            if (!$role) {
                $role = new Role($systemRole->value, $systemRole->label(), true);
                $role->setDescription('Rôle système');
                $this->roleRepository->save($role, false);
            }
            $role->clearPermissions();
            foreach ($systemRole->defaultPermissions() as $perm) {
                $role->addPermission($permissions[$perm->value]);
            }
            $this->roleRepository->save($role, false);
        }

        $this->entityManager->flush();

        $adminEmail = $_ENV['SEED_ADMIN_EMAIL'] ?? 'admin@onreach.inovixora.fr';
        $adminPassword = $_ENV['SEED_ADMIN_PASSWORD'] ?? 'Admin@OnReach12!';

        $admin = $this->userRepository->findByEmail($adminEmail);
        if (!$admin) {
            $admin = new User($adminEmail, 'Super', 'Admin');
            $admin->setPassword($this->passwordHasher->hashPassword($admin, $adminPassword));
            $superAdminRole = $this->roleRepository->findByCode(SystemRole::SUPER_ADMIN->value);
            if ($superAdminRole) {
                $admin->addRole($superAdminRole);
            }
            $this->userRepository->save($admin);
            $io->success(sprintf('Super admin créé : %s', $adminEmail));
        } else {
            $io->note(sprintf('Super admin existant : %s', $adminEmail));
        }

        $io->success('RBAC seed terminé.');

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Security;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use App\Infrastructure\Security\PermissionVoter;
use App\Tests\Support\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final class PermissionVoterTest extends KernelTestCase
{
    use DatabaseTestTrait;

    private AccessDecisionManagerInterface $accessDecisionManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabase();
        $this->seedRbac();
        $this->accessDecisionManager = static::getContainer()->get(AccessDecisionManagerInterface::class);
    }

    #[DataProvider('adminPermissionsProvider')]
    public function testSuperAdminIsGrantedAllPermissions(string $permission): void
    {
        $user = static::getContainer()->get(\App\Repository\UserRepository::class)
            ->findByEmail('admin@onreach.inovixora.fr');
        self::assertInstanceOf(User::class, $user);

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        self::assertTrue(
            $this->accessDecisionManager->decide($token, [$permission]),
            "SUPER_ADMIN should be granted {$permission}",
        );
    }

    public function testUserWithoutPermissionIsDenied(): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        $role = new Role('TEST_ROLE', 'Test', false);
        $permission = new Permission('test.only', 'Test only', false);
        $role->addPermission($permission);
        $em->persist($role);
        $em->persist($permission);

        $user = new User('limited@example.com', 'Limited', 'User');
        $user->setPassword('hashed');
        $user->addRole($role);
        $em->persist($user);
        $em->flush();

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        self::assertFalse($this->accessDecisionManager->decide($token, ['users.view']));
        self::assertTrue($this->accessDecisionManager->decide($token, ['test.only']));
    }

    public function testNonPermissionAttributeIsAbstained(): void
    {
        $voter = new PermissionVoter();
        $user = new User('x@example.com', 'X', 'Y');
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);

        $reflection = new \ReflectionClass($voter);
        $supports = $reflection->getMethod('supports');
        $supports->setAccessible(true);

        self::assertFalse($supports->invoke($voter, 'ROLE_ADMIN', null));
        self::assertTrue($supports->invoke($voter, 'users.view', null));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function adminPermissionsProvider(): iterable
    {
        foreach ([
            'users.view', 'users.create', 'roles.view', 'permissions.edit', 'system.logs',
        ] as $permission) {
            yield $permission => [$permission];
        }
    }
}

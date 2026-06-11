<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\ORM\Tools\SchemaTool;

trait DatabaseTestTrait
{
    protected function resetDatabase(): void
    {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    protected function seedRbac(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedRbacCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }

    protected function seedChecklist(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedChecklistCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}

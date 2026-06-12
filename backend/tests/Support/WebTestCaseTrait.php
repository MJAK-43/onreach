<?php

declare(strict_types=1);

namespace App\Tests\Support;

trait WebTestCaseTrait
{
    protected function setUpWebTestCase(): void
    {
        static::ensureKernelShutdown();
    }

    protected function tearDownWebTestCase(): void
    {
        static::ensureKernelShutdown();
    }
}

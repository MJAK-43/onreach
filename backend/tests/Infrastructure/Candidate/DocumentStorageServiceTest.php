<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Candidate;

use App\Infrastructure\Candidate\DocumentStorageService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocumentStorageServiceTest extends KernelTestCase
{
    public function testStoreCreatesFileOnDisk(): void
    {
        self::bootKernel();
        $service = static::getContainer()->get(DocumentStorageService::class);

        $tmp = tempnam(sys_get_temp_dir(), 'doc');
        file_put_contents($tmp, 'test content');
        $upload = new UploadedFile($tmp, 'passeport.pdf', 'application/pdf', null, true);

        $path = $service->store($upload, 'candidate-id', 'document-id');
        self::assertStringContainsString('candidates/candidate-id/', $path);
        self::assertFileExists($service->getAbsolutePath($path));

        unlink($service->getAbsolutePath($path));
    }
}

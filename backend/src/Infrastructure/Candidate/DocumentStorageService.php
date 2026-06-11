<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class DocumentStorageService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private string $bucket = 'documents',
    ) {
    }

    public function store(UploadedFile $file, string $candidateId, string $documentId): string
    {
        $extension = $file->guessExtension() ?? 'bin';
        $relativePath = sprintf('candidates/%s/%s.%s', $candidateId, $documentId, $extension);
        $absolutePath = $this->getStorageRoot().'/'.$relativePath;
        $directory = \dirname($absolutePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Impossible de créer le répertoire de stockage.');
        }
        $file->move($directory, basename($absolutePath));

        return $relativePath;
    }

    public function getAbsolutePath(string $storagePath): string
    {
        return $this->getStorageRoot().'/'.$storagePath;
    }

    public function delete(string $storagePath): void
    {
        $path = $this->getAbsolutePath($storagePath);
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function getStorageRoot(): string
    {
        return $this->projectDir.'/var/storage/'.$this->bucket;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class DocumentUploadValidator
{
    private const MAX_BYTES = 10 * 1024 * 1024;

    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
    ];

    public function validate(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new BadRequestHttpException('Fichier invalide.');
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new BadRequestHttpException('Taille maximale : 10 Mo.');
        }

        $mime = $file->getClientMimeType() ?: 'application/octet-stream';
        if (!\in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new BadRequestHttpException('Type de fichier non autorisé.');
        }
    }
}

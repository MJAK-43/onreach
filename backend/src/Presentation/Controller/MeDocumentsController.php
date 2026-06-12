<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\User;
use App\Infrastructure\Candidate\CandidateDocumentManager;
use App\Infrastructure\Candidate\CandidateResolver;
use App\Infrastructure\Candidate\DocumentStorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me/documents')]
final class MeDocumentsController extends AbstractController
{
    public function __construct(
        private readonly CandidateResolver $candidateResolver,
        private readonly CandidateDocumentManager $documentManager,
        private readonly DocumentStorageService $storageService,
    ) {
    }

    #[Route('', name: 'me_documents_list', methods: ['GET'])]
    #[IsGranted('documents.view')]
    public function list(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->documentManager->listDocuments($candidate));
    }

    #[Route('', name: 'me_documents_upload', methods: ['POST'])]
    #[IsGranted('documents.upload')]
    public function upload(Request $request): JsonResponse
    {
        $candidate = $this->resolveCandidate();
        $file = $request->files->get('file');
        $type = $request->request->getString('type');
        if (!$file || '' === $type) {
            throw new BadRequestHttpException('Fichier et type requis.');
        }

        $replaceId = $request->request->get('replaceDocumentId');
        $document = $this->documentManager->upload(
            $candidate,
            $file,
            $type,
            \is_string($replaceId) && '' !== $replaceId ? $replaceId : null,
        );

        return new JsonResponse($document, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'me_documents_replace', methods: ['PUT'])]
    #[IsGranted('documents.upload')]
    public function replace(string $id, Request $request): JsonResponse
    {
        $candidate = $this->resolveCandidate();
        $file = $request->files->get('file');
        $type = $request->request->getString('type', 'other');
        if (!$file) {
            throw new BadRequestHttpException('Fichier requis.');
        }

        $document = $this->documentManager->upload($candidate, $file, $type, $id);

        return new JsonResponse($document);
    }

    #[Route('/{id}', name: 'me_documents_delete', methods: ['DELETE'])]
    #[IsGranted('documents.upload')]
    public function delete(string $id): Response
    {
        $candidate = $this->resolveCandidate();
        $this->documentManager->delete($candidate, $id);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/preview', name: 'me_documents_preview', methods: ['GET'])]
    #[IsGranted('documents.view')]
    public function preview(string $id): BinaryFileResponse
    {
        return $this->fileResponse($id, true);
    }

    #[Route('/{id}/download', name: 'me_documents_download', methods: ['GET'])]
    #[IsGranted('documents.view')]
    public function download(string $id): BinaryFileResponse
    {
        return $this->fileResponse($id, false);
    }

    private function fileResponse(string $id, bool $inline): BinaryFileResponse
    {
        $candidate = $this->resolveCandidate();
        $document = $this->documentManager->getOwnedDocument($candidate, $id);
        $path = $document->getStoragePath();
        if (null === $path) {
            throw new BadRequestHttpException('Fichier non disponible.');
        }

        $filePath = $this->storageService->getAbsolutePath($path);
        if (!is_file($filePath)) {
            throw new BadRequestHttpException('Fichier introuvable sur le stockage.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            $inline ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            (string) ($document->getOriginalFilename() ?? 'document'),
        );

        return $response;
    }

    private function resolveCandidate(): \App\Entity\Candidate
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->candidateResolver->resolveForUser($user);
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Domain\Candidate\Enum\ApplicationType;
use App\Domain\Candidate\Enum\DocumentStatus;
use App\Entity\Candidate;
use App\Entity\ChecklistProgress;
use App\Repository\ChecklistItemRepository;
use App\Repository\ChecklistProgressRepository;
use App\Repository\ChecklistTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ChecklistService
{
    public function __construct(
        private ChecklistTemplateRepository $templateRepository,
        private ChecklistItemRepository $itemRepository,
        private ChecklistProgressRepository $progressRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{items: list<array<string, mixed>>, percent: int}
     */
    public function getProgressForCandidate(Candidate $candidate, ApplicationType $type): array
    {
        $template = $this->templateRepository->findOneBy(['code' => $type->value]);
        if (null === $template) {
            return ['items' => [], 'percent' => 0];
        }

        $items = $this->itemRepository->findBy(['template' => $template], ['sortOrder' => 'ASC']);
        $progressRows = $this->progressRepository->findIndexedForCandidate($candidate);
        $result = [];
        $completed = 0;

        foreach ($items as $item) {
            $progress = $progressRows[$item->getId()->toRfc4122()] ?? null;
            $isDone = $progress?->isCompleted() ?? $this->isDocumentValidated($candidate, $item->getDocumentType());
            if ($isDone) {
                ++$completed;
            }
            $result[] = [
                'id' => $item->getId()->toRfc4122(),
                'label' => $item->getLabel(),
                'documentType' => $item->getDocumentType()->value,
                'required' => $item->isRequired(),
                'completed' => $isDone,
            ];
        }

        $total = count($items);
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return ['items' => $result, 'percent' => $percent];
    }

    public function syncProgress(Candidate $candidate, ApplicationType $type): void
    {
        $data = $this->getProgressForCandidate($candidate, $type);
        foreach ($data['items'] as $row) {
            $item = $this->itemRepository->find($row['id']);
            if (null === $item) {
                continue;
            }
            $progress = $this->progressRepository->findOneBy([
                'candidate' => $candidate,
                'checklistItem' => $item,
            ]);
            if (null === $progress) {
                $progress = new ChecklistProgress($candidate, $item);
                $progress->setCompleted($row['completed']);
                $this->progressRepository->save($progress, false);
            } else {
                $progress->setCompleted($row['completed']);
            }
        }
        $this->entityManager->flush();
    }

    private function isDocumentValidated(Candidate $candidate, \App\Domain\Candidate\Enum\DocumentType $type): bool
    {
        foreach ($candidate->getDocuments() as $document) {
            if ($document->getType() === $type && DocumentStatus::VALIDATED === $document->getStatus()) {
                return true;
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\PathwaySubStepTemplate;
use App\Entity\PathwayTemplate;
use App\Repository\PathwayTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class PathwayCalendarImportService
{
    public function __construct(
        private PathwayTemplateRepository $templateRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{suggestions: list<array<string, mixed>>, applied: int}
     */
    public function import(string $templateId, string $calendarText, bool $apply = false): array
    {
        if (!Uuid::isValid($templateId)) {
            throw new NotFoundHttpException('Template introuvable.');
        }

        $template = $this->templateRepository->find(Uuid::fromString($templateId));
        if (!$template instanceof PathwayTemplate) {
            throw new NotFoundHttpException('Template introuvable.');
        }

        $lines = preg_split('/\R/u', trim($calendarText)) ?: [];
        $campaignStart = $template->getCampaign()->getStartDate();
        $suggestions = [];
        $applied = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ('' === $line || str_starts_with($line, '#')) {
                continue;
            }

            $parsed = $this->parseLine($line, $campaignStart);
            if (!$parsed) {
                continue;
            }

            $match = $this->findBestSubStepMatch($template, $parsed['title']);
            if (!$match instanceof PathwaySubStepTemplate) {
                continue;
            }

            $suggestions[] = [
                'subStepId' => $match->getId()->toRfc4122(),
                'subStepTitle' => $match->getTitle(),
                'suggestedDueOffsetDays' => $parsed['offsetDays'],
                'sourceLine' => $line,
            ];

            if ($apply) {
                $match->setDefaultDueOffsetDays($parsed['offsetDays']);
                ++$applied;
            }
        }

        if ($apply && $applied > 0) {
            $this->entityManager->flush();
        }

        return [
            'suggestions' => $suggestions,
            'applied' => $applied,
        ];
    }

    /**
     * @return array{title: string, offsetDays: int}|null
     */
    private function parseLine(string $line, \DateTimeImmutable $campaignStart): ?array
    {
        if (preg_match(
            '/^(?:(\d{1,2})[\/\-.](\d{1,2})(?:[\/\-.](\d{2,4}))?\s*[-–:]\s*)?(.+)$/u',
            $line,
            $matches,
        )) {
            $title = trim($matches[4]);
            if ('' === $title) {
                return null;
            }

            if (!empty($matches[1]) && !empty($matches[2])) {
                $day = (int) $matches[1];
                $month = (int) $matches[2];
                $year = !empty($matches[3]) ? (int) $matches[3] : (int) $campaignStart->format('Y');
                if ($year < 100) {
                    $year += 2000;
                }

                try {
                    $dueDate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
                    $offsetDays = (int) $campaignStart->diff($dueDate)->format('%r%a');

                    return ['title' => $title, 'offsetDays' => max(0, $offsetDays)];
                } catch (\Exception) {
                    return ['title' => $title, 'offsetDays' => 0];
                }
            }

            return ['title' => $title, 'offsetDays' => 0];
        }

        return null;
    }

    private function findBestSubStepMatch(PathwayTemplate $template, string $title): ?PathwaySubStepTemplate
    {
        $normalized = mb_strtolower($title);
        $best = null;
        $bestScore = 0;

        foreach ($template->getStages() as $stage) {
            foreach ($stage->getSubSteps() as $subStep) {
                $candidate = mb_strtolower($subStep->getTitle());
                similar_text($normalized, $candidate, $score);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $subStep;
                }
            }
        }

        return $bestScore >= 45 ? $best : null;
    }
}

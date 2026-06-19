<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\Campaign;
use App\Entity\PathwayStageTemplate;
use App\Entity\PathwaySubStepTemplate;
use App\Entity\PathwayTemplate;
use App\Repository\CampaignRepository;
use App\Repository\PathwayStageTemplateRepository;
use App\Repository\PathwaySubStepTemplateRepository;
use App\Repository\PathwayTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class PathwayTemplateAdminService
{
    public function __construct(
        private CampaignRepository $campaignRepository,
        private PathwayTemplateRepository $templateRepository,
        private PathwayStageTemplateRepository $stageRepository,
        private PathwaySubStepTemplateRepository $subStepRepository,
        private PathwayTemplateSerializer $serializer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listTemplates(?string $campaignId): array
    {
        if ($campaignId && Uuid::isValid($campaignId)) {
            $campaign = $this->campaignRepository->find(Uuid::fromString($campaignId));
        } else {
            $campaign = $this->campaignRepository->findActive();
        }

        if (!$campaign instanceof Campaign) {
            return [];
        }

        $templates = $this->templateRepository->findBy(['campaign' => $campaign], ['name' => 'ASC']);

        return array_map(fn (PathwayTemplate $template) => $this->serializer->serializeSummary($template), $templates);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTemplate(string $templateId): array
    {
        $template = $this->loadTemplate($templateId);

        return $this->serializer->serializeDetail($template);
    }

    /**
     * @param array{name?: string} $payload
     *
     * @return array<string, mixed>
     */
    public function patchTemplate(string $templateId, array $payload): array
    {
        $template = $this->loadTemplate($templateId);
        if (isset($payload['name'])) {
            $name = trim((string) $payload['name']);
            if ('' === $name) {
                throw new BadRequestHttpException('Le nom ne peut pas être vide.');
            }
            $template->setName($name);
        }

        $this->entityManager->flush();

        return $this->serializer->serializeDetail($template);
    }

    /**
     * @param array{title?: string, description?: string|null} $payload
     *
     * @return array<string, mixed>
     */
    public function patchStage(string $stageId, array $payload): array
    {
        $stage = $this->loadStage($stageId);
        if (isset($payload['title'])) {
            $title = trim((string) $payload['title']);
            if ('' === $title) {
                throw new BadRequestHttpException('Le titre ne peut pas être vide.');
            }
            $stage->setTitle($title);
        }
        if (\array_key_exists('description', $payload)) {
            $stage->setDescription(null !== $payload['description'] ? trim((string) $payload['description']) : null);
        }

        $this->entityManager->flush();

        return $this->serializer->serializeDetail($stage->getPathwayTemplate());
    }

    /**
     * @param array{
     *     title?: string,
     *     description?: string|null,
     *     required?: bool,
     *     defaultDueOffsetDays?: int|null
     * } $payload
     *
     * @return array<string, mixed>
     */
    public function patchSubStep(string $subStepId, array $payload): array
    {
        $subStep = $this->loadSubStep($subStepId);
        if (isset($payload['title'])) {
            $title = trim((string) $payload['title']);
            if ('' === $title) {
                throw new BadRequestHttpException('Le titre ne peut pas être vide.');
            }
            $subStep->setTitle($title);
        }
        if (\array_key_exists('description', $payload)) {
            $subStep->setDescription(null !== $payload['description'] ? trim((string) $payload['description']) : null);
        }
        if (isset($payload['required'])) {
            $subStep->setRequired((bool) $payload['required']);
        }
        if (\array_key_exists('defaultDueOffsetDays', $payload)) {
            $subStep->setDefaultDueOffsetDays(null !== $payload['defaultDueOffsetDays'] ? (int) $payload['defaultDueOffsetDays'] : null);
        }

        $this->entityManager->flush();

        return $this->serializer->serializeDetail($subStep->getStageTemplate()->getPathwayTemplate());
    }

    /**
     * @param array{name: string, year: int, startDate: string, endDate: string, cloneFromActive?: bool} $payload
     *
     * @return array<string, mixed>
     */
    public function createCampaign(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $year = (int) ($payload['year'] ?? 0);
        if ('' === $name || $year < 2020) {
            throw new BadRequestHttpException('Nom et année valides requis.');
        }

        if ($this->campaignRepository->findOneBy(['year' => $year])) {
            throw new BadRequestHttpException('Une campagne existe déjà pour cette année.');
        }

        $startDate = new \DateTimeImmutable((string) ($payload['startDate'] ?? sprintf('%d-01-01', $year)));
        $endDate = new \DateTimeImmutable((string) ($payload['endDate'] ?? sprintf('%d-12-31', $year)));

        foreach ($this->campaignRepository->findAll() as $existing) {
            $existing->setActive(false);
        }

        $campaign = new Campaign($name, $year, $startDate, $endDate);
        $campaign->setActive(true);
        $this->entityManager->persist($campaign);

        if (($payload['cloneFromActive'] ?? true) === true) {
            $this->cloneTemplatesFromDefinitions($campaign);
        }

        $this->entityManager->flush();

        return $this->serializeCampaign($campaign);
    }

    /**
     * @param array{name?: string, active?: bool, startDate?: string, endDate?: string} $payload
     *
     * @return array<string, mixed>
     */
    public function patchCampaign(string $campaignId, array $payload): array
    {
        $campaign = $this->loadCampaign($campaignId);

        if (isset($payload['name'])) {
            $campaign->setName(trim((string) $payload['name']));
        }
        if (isset($payload['startDate'])) {
            $campaign->setStartDate(new \DateTimeImmutable((string) $payload['startDate']));
        }
        if (isset($payload['endDate'])) {
            $campaign->setEndDate(new \DateTimeImmutable((string) $payload['endDate']));
        }
        if (isset($payload['active']) && (bool) $payload['active']) {
            foreach ($this->campaignRepository->findAll() as $existing) {
                $existing->setActive(false);
            }
            $campaign->setActive(true);
        }

        $this->entityManager->flush();

        return $this->serializeCampaign($campaign);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listCampaigns(): array
    {
        $campaigns = $this->campaignRepository->findBy([], ['year' => 'DESC']);

        return array_map(fn (Campaign $campaign) => $this->serializeCampaign($campaign), $campaigns);
    }

    private function cloneTemplatesFromDefinitions(Campaign $campaign): void
    {
        foreach (PathwayTemplateDefinitions::all() as $definition) {
            $template = new PathwayTemplate(
                $campaign,
                $definition['code'],
                $definition['name'],
                $definition['eligibleStudyTypes'],
            );
            $this->entityManager->persist($template);

            foreach ($definition['stages'] as $stageIndex => $stageDef) {
                $stage = new PathwayStageTemplate(
                    $template,
                    $stageDef['title'],
                    $stageDef['description'],
                    $stageIndex + 1,
                );
                $this->entityManager->persist($stage);

                foreach ($stageDef['subSteps'] as $subIndex => $subDef) {
                    $this->entityManager->persist(new PathwaySubStepTemplate(
                        $stage,
                        $subDef['title'],
                        $subDef['description'],
                        $subDef['required'],
                        $subDef['dueOffsetDays'] ?? null,
                        $subIndex + 1,
                    ));
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCampaign(Campaign $campaign): array
    {
        return [
            'id' => $campaign->getId()->toRfc4122(),
            'name' => $campaign->getName(),
            'year' => $campaign->getYear(),
            'startDate' => $campaign->getStartDate()->format('Y-m-d'),
            'endDate' => $campaign->getEndDate()->format('Y-m-d'),
            'active' => $campaign->isActive(),
            'templateCount' => $campaign->getPathwayTemplates()->count(),
        ];
    }

    private function loadCampaign(string $campaignId): Campaign
    {
        if (!Uuid::isValid($campaignId)) {
            throw new NotFoundHttpException('Campagne introuvable.');
        }

        $campaign = $this->campaignRepository->find(Uuid::fromString($campaignId));
        if (!$campaign instanceof Campaign) {
            throw new NotFoundHttpException('Campagne introuvable.');
        }

        return $campaign;
    }

    private function loadTemplate(string $templateId): PathwayTemplate
    {
        if (!Uuid::isValid($templateId)) {
            throw new NotFoundHttpException('Template introuvable.');
        }

        $template = $this->templateRepository->find(Uuid::fromString($templateId));
        if (!$template instanceof PathwayTemplate) {
            throw new NotFoundHttpException('Template introuvable.');
        }

        return $template;
    }

    private function loadStage(string $stageId): PathwayStageTemplate
    {
        if (!Uuid::isValid($stageId)) {
            throw new NotFoundHttpException('Étape introuvable.');
        }

        $stage = $this->stageRepository->find(Uuid::fromString($stageId));
        if (!$stage instanceof PathwayStageTemplate) {
            throw new NotFoundHttpException('Étape introuvable.');
        }

        return $stage;
    }

    private function loadSubStep(string $subStepId): PathwaySubStepTemplate
    {
        if (!Uuid::isValid($subStepId)) {
            throw new NotFoundHttpException('Sous-étape introuvable.');
        }

        $subStep = $this->subStepRepository->find(Uuid::fromString($subStepId));
        if (!$subStep instanceof PathwaySubStepTemplate) {
            throw new NotFoundHttpException('Sous-étape introuvable.');
        }

        return $subStep;
    }
}

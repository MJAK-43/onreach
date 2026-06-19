<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Entity\Campaign;
use App\Entity\PathwaySetting;
use App\Entity\PathwayStageTemplate;
use App\Entity\PathwaySubStepTemplate;
use App\Entity\PathwayTemplate;
use App\Infrastructure\Pathway\PathwayTemplateDefinitions;
use App\Repository\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed:pathways', description: 'Seed campagne 2026 et parcours Campus France, Parcoursup, Paris-Saclay')]
final class SeedPathwaysCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CampaignRepository $campaignRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $campaign = $this->campaignRepository->findOneBy(['year' => 2026]);
        if (!$campaign instanceof Campaign) {
            foreach ($this->campaignRepository->findAll() as $existing) {
                $existing->setActive(false);
            }

            $campaign = new Campaign(
                'Campagne 2026',
                2026,
                new \DateTimeImmutable('2026-01-01'),
                new \DateTimeImmutable('2026-12-31'),
            );
            $campaign->setActive(true);
            $this->entityManager->persist($campaign);
            $io->success('Campagne 2026 créée.');
        } else {
            $campaign->setActive(true);
            $io->note('Campagne 2026 existante — mise à jour des templates si absents.');
        }

        foreach (PathwayTemplateDefinitions::all() as $definition) {
            $this->ensurePathwayTemplate($campaign, $definition);
        }

        foreach (PathwayCode::cases() as $code) {
            $existing = $this->entityManager->getRepository(PathwaySetting::class)->findOneBy(['pathwayCode' => $code]);
            if (!$existing instanceof PathwaySetting) {
                $this->entityManager->persist(new PathwaySetting($code, false));
            }
        }

        $this->entityManager->flush();
        $io->success('Parcours 2026 prêts (Campus France, Parcoursup, Paris-Saclay).');

        return Command::SUCCESS;
    }

    /**
     * @param array{
     *     code: \App\Domain\Pathway\Enum\PathwayCode,
     *     name: string,
     *     eligibleStudyTypes: list<\App\Domain\Pathway\Enum\StudyApplicationType>,
     *     stages: list<array{
     *         title: string,
     *         description: string|null,
     *         subSteps: list<array{title: string, description: string|null, required: bool, dueOffsetDays: int|null}>
     *     }>
     * } $definition
     */
    private function ensurePathwayTemplate(Campaign $campaign, array $definition): void
    {
        $existing = $this->entityManager->getRepository(PathwayTemplate::class)->findOneBy([
            'campaign' => $campaign,
            'code' => $definition['code'],
        ]);

        if ($existing instanceof PathwayTemplate) {
            return;
        }

        $template = new PathwayTemplate(
            $campaign,
            $definition['code'],
            $definition['name'],
            $definition['eligibleStudyTypes'],
        );
        $this->entityManager->persist($template);
        $campaign->addPathwayTemplate($template);

        foreach ($definition['stages'] as $stageIndex => $stageDef) {
            $stage = new PathwayStageTemplate(
                $template,
                $stageDef['title'],
                $stageDef['description'],
                $stageIndex + 1,
            );
            $template->addStage($stage);
            $this->entityManager->persist($stage);

            foreach ($stageDef['subSteps'] as $subIndex => $subDef) {
                $subStep = new PathwaySubStepTemplate(
                    $stage,
                    $subDef['title'],
                    $subDef['description'],
                    $subDef['required'],
                    $subDef['dueOffsetDays'],
                    $subIndex + 1,
                );
                $stage->addSubStep($subStep);
                $this->entityManager->persist($subStep);
            }
        }
    }
}

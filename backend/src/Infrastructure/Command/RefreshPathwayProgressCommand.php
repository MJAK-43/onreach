<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Entity\CandidatePathway;
use App\Infrastructure\Pathway\PathwayProgressCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:pathway:refresh-progress', description: 'Recalcule la progression de tous les parcours candidats')]
final class RefreshPathwayProgressCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PathwayProgressCalculator $progressCalculator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pathways = $this->entityManager->getRepository(CandidatePathway::class)->findAll();

        foreach ($pathways as $pathway) {
            $this->progressCalculator->refresh($pathway);
        }

        $this->entityManager->flush();
        $io->success(sprintf('Progression recalculée pour %d parcours.', \count($pathways)));

        return Command::SUCCESS;
    }
}

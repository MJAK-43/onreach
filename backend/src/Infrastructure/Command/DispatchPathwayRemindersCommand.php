<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Application\Message\PathwayDueDateReminderMessage;
use App\Repository\CandidatePathwaySubStepRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:pathways:dispatch-reminders',
    description: 'Planifie les relances in-app pour les sous-étapes dont l\'échéance est proche ou dépassée.',
)]
final class DispatchPathwayRemindersCommand extends Command
{
    public function __construct(
        private readonly CandidatePathwaySubStepRepository $subStepRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('days-ahead', null, InputOption::VALUE_REQUIRED, 'Jours avant échéance', '3');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $daysAhead = max(0, (int) $input->getOption('days-ahead'));
        $until = new \DateTimeImmutable(sprintf('+%d days', $daysAhead));

        $subSteps = $this->subStepRepository->findDueForReminder($until);
        foreach ($subSteps as $subStep) {
            $this->messageBus->dispatch(new PathwayDueDateReminderMessage($subStep->getId()->toRfc4122()));
        }

        $io->success(sprintf('%d relance(s) planifiée(s).', \count($subSteps)));

        return Command::SUCCESS;
    }
}

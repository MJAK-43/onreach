<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Entity\CounselorAvailabilitySlot;
use App\Entity\User;
use App\Repository\CounselorAvailabilitySlotRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed:appointment-slots', description: 'Seed créneaux disponibles pour les conseillers démo')]
final class SeedAppointmentSlotsCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private CounselorAvailabilitySlotRepository $slotRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $counselorEmail = $_ENV['SEED_COUNSELOR_EMAIL'] ?? 'marie.kouassi@onreach.inovixora.fr';
        $counselor = $this->userRepository->findByEmail($counselorEmail);

        if (!$counselor instanceof User) {
            $io->warning('Conseillère démo introuvable — exécutez app:seed:demo-users.');

            return Command::SUCCESS;
        }

        $from = new \DateTimeImmutable('today');
        if ($this->slotRepository->countForCounselorFrom($counselor, $from) > 0) {
            $io->note('Créneaux déjà présents pour la conseillère démo.');

            return Command::SUCCESS;
        }

        $created = 0;
        for ($day = 1; $day <= 21; ++$day) {
            $date = $from->modify("+{$day} days");
            if ((int) $date->format('N') >= 6) {
                continue;
            }

            foreach (['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'] as $time) {
                $startsAt = new \DateTimeImmutable($date->format('Y-m-d').' '.$time);
                $endsAt = $startsAt->modify('+30 minutes');
                $this->slotRepository->save(new CounselorAvailabilitySlot($counselor, $startsAt, $endsAt), false);
                ++$created;
            }
        }

        if ($created > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('%d créneaux générés pour %s.', $created, $counselorEmail));

        return Command::SUCCESS;
    }
}

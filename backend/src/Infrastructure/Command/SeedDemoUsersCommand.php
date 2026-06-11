<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\Candidate\Enum\CandidateStatus;
use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Domain\User\Enum\SystemRole;
use App\Entity\Candidate;
use App\Entity\CandidateDocument;
use App\Entity\CampusFranceApplication;
use App\Entity\User;
use App\Infrastructure\Candidate\CandidateReferenceGenerator;
use App\Infrastructure\Candidate\CandidateTimelineService;
use App\Repository\CandidateRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed:demo-users', description: 'Seed conseiller, candidat et dossier de démonstration')]
final class SeedDemoUsersCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private CandidateRepository $candidateRepository,
        private CandidateReferenceGenerator $referenceGenerator,
        private CandidateTimelineService $timelineService,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $counselorEmail = $_ENV['SEED_COUNSELOR_EMAIL'] ?? 'marie.kouassi@onreach.inovixora.fr';
        $counselorPassword = $_ENV['SEED_COUNSELOR_PASSWORD'] ?? 'Counselor@OnReach12!';
        $candidateUserEmail = $_ENV['SEED_CANDIDATE_USER_EMAIL'] ?? 'mohamed.koffi@onreach.inovixora.fr';
        $candidateUserPassword = $_ENV['SEED_CANDIDATE_USER_PASSWORD'] ?? 'Candidate@OnReach12!';

        $counselorRole = $this->roleRepository->findByCode(SystemRole::COUNSELOR->value);
        $candidateRole = $this->roleRepository->findByCode(SystemRole::CANDIDATE->value);

        if (!$counselorRole || !$candidateRole) {
            $io->error('Exécutez app:seed:rbac avant app:seed:demo-users.');

            return Command::FAILURE;
        }

        $counselor = $this->userRepository->findByEmail($counselorEmail);
        if (!$counselor) {
            $counselor = new User($counselorEmail, 'Marie', 'Kouassi');
            $counselor->setPassword($this->passwordHasher->hashPassword($counselor, $counselorPassword));
            $counselor->addRole($counselorRole);
            $this->userRepository->save($counselor, false);
            $io->success(sprintf('Conseillère créée : %s / %s', $counselorEmail, $counselorPassword));
        } else {
            $io->note(sprintf('Conseillère existante : %s', $counselorEmail));
        }

        $candidateUser = $this->userRepository->findByEmail($candidateUserEmail);
        if (!$candidateUser) {
            $candidateUser = new User($candidateUserEmail, 'Mohamed', 'Koffi');
            $candidateUser->setPassword($this->passwordHasher->hashPassword($candidateUser, $candidateUserPassword));
            $candidateUser->addRole($candidateRole);
            $this->userRepository->save($candidateUser, false);
            $io->success(sprintf('Compte candidat créé : %s / %s', $candidateUserEmail, $candidateUserPassword));
        } else {
            $io->note(sprintf('Compte candidat existant : %s', $candidateUserEmail));
        }

        $this->entityManager->flush();

        $dossier = $this->candidateRepository->findOneBy(['email' => $candidateUserEmail]);
        if (!$dossier) {
            $dossier = new Candidate('Mohamed', 'Koffi', $candidateUserEmail, 'Côte d\'Ivoire');
            $dossier->setReferenceNumber($this->referenceGenerator->generate());
            $dossier->setStatus(CandidateStatus::ADMISSION_OBTAINED);
            $dossier->setPhone('+225 07 00 00 00 00');
            $dossier->setCity('Abidjan');
            $dossier->setCountry('Côte d\'Ivoire');
            $dossier->setAssignedCounselor($counselor);

            $passport = new CandidateDocument($dossier, DocumentType::PASSPORT, DocumentStatus::VALIDATED);
            $cv = new CandidateDocument($dossier, DocumentType::CV, DocumentStatus::VALIDATED);
            $dossier->addDocument($passport);
            $dossier->addDocument($cv);

            $campusFrance = new CampusFranceApplication();
            $campusFrance->setStudyProject('Master en informatique à Paris');
            $dossier->setCampusFranceApplication($campusFrance);

            $this->candidateRepository->save($dossier, false);
            $this->timelineService->record($dossier, 'candidate.created', 'Dossier candidat créé');
            $this->timelineService->record($dossier, 'candidate.status_changed', 'Admission obtenue');
            $io->success('Dossier démo Mohamed Koffi créé.');
        } else {
            if (null === $dossier->getAssignedCounselor()) {
                $dossier->setAssignedCounselor($counselor);
            }
            $io->note('Dossier démo Mohamed Koffi existant.');
        }

        $this->seedExtraCandidates($counselor);
        $this->entityManager->flush();

        $io->success('Utilisateurs et dossiers de démonstration prêts.');

        return Command::SUCCESS;
    }

    private function seedExtraCandidates(User $counselor): void
    {
        $samples = [
            ['Aissatou', 'Diallo', 'aissatou.diallo@demo.fr', 'Sénégal', CandidateStatus::IN_PROGRESS],
            ['Fatou', 'Sow', 'fatou.sow@demo.fr', 'Sénégal', CandidateStatus::DOCUMENTS_PENDING],
            ['Jean', 'Mbarga', 'jean.mbarga@demo.fr', 'Cameroun', CandidateStatus::VISA_OBTAINED],
        ];

        foreach ($samples as [$first, $last, $email, $nationality, $status]) {
            if ($this->candidateRepository->findOneBy(['email' => $email])) {
                continue;
            }
            $candidate = new Candidate($first, $last, $email, $nationality);
            $candidate->setReferenceNumber($this->referenceGenerator->generate());
            $candidate->setStatus($status);
            $candidate->setAssignedCounselor($counselor);
            $this->candidateRepository->save($candidate);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\Candidate\Enum\CampusFranceStatus;
use App\Domain\Candidate\Enum\CandidateStatus;
use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Domain\Candidate\Enum\ParcoursupWishStatus;
use App\Domain\Candidate\Enum\ParisSaclayDegreeLevel;
use App\Domain\Candidate\Enum\ParisSaclayStatus;
use App\Domain\Pathway\Enum\PathwayCode;
use App\Domain\Pathway\Enum\StudyApplicationType;
use App\Domain\User\Enum\SystemRole;
use App\Entity\CampusFranceApplication;
use App\Entity\Candidate;
use App\Entity\CandidateDocument;
use App\Entity\CandidatePathway;
use App\Entity\ParcoursupApplication;
use App\Entity\ParcoursupWish;
use App\Entity\ParisSaclayApplication;
use App\Entity\User;
use App\Infrastructure\Candidate\CandidateReferenceGenerator;
use App\Infrastructure\Candidate\CandidateTimelineService;
use App\Infrastructure\Pathway\PathwayAssignmentService;
use App\Infrastructure\Pathway\PathwayProgressCalculator;
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
        private PathwayAssignmentService $pathwayAssignmentService,
        private PathwayProgressCalculator $pathwayProgressCalculator,
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
        $continuingUserEmail = $_ENV['SEED_CANDIDATE_CONTINUING_EMAIL'] ?? 'ama.diallo@onreach.inovixora.fr';
        $continuingUserPassword = $_ENV['SEED_CANDIDATE_CONTINUING_PASSWORD'] ?? 'Continuing@OnReach12!';

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
            $dossier->setStudyApplicationType(StudyApplicationType::FIRST_YEAR);

            $passport = new CandidateDocument($dossier, DocumentType::PASSPORT, DocumentStatus::VALIDATED);
            $cv = new CandidateDocument($dossier, DocumentType::CV, DocumentStatus::VALIDATED);
            $dossier->addDocument($passport);
            $dossier->addDocument($cv);

            $campusFrance = new CampusFranceApplication();
            $campusFrance->setStudyProject('Master en informatique à Paris');
            $campusFrance->setStatus(CampusFranceStatus::ADMISSION_OBTAINED);
            $dossier->setCampusFranceApplication($campusFrance);

            $parcoursup = new ParcoursupApplication();
            $parcoursup->setIneNumber('123456789AB');
            $parcoursup->setHighSchool('Lycée Moderne d\'Abidjan');
            $parcoursup->setMotivationProject('Projet d\'études en informatique et IA');
            $wish1 = new ParcoursupWish(1, 'Université Paris-Saclay', 'Licence Informatique', ParcoursupWishStatus::ACCEPTE);
            $wish1->setSubmittedAt(new \DateTimeImmutable('-30 days'));
            $parcoursup->addWish($wish1);
            $wish2 = new ParcoursupWish(2, 'Sorbonne Université', 'Licence Mathématiques', ParcoursupWishStatus::LISTE_ATTENTE);
            $wish2->setSubmittedAt(new \DateTimeImmutable('-28 days'));
            $parcoursup->addWish($wish2);
            $dossier->setParcoursupApplication($parcoursup);

            $motivation = new CandidateDocument($dossier, DocumentType::MOTIVATION_LETTER, DocumentStatus::UPLOADED);
            $transcript = new CandidateDocument($dossier, DocumentType::TRANSCRIPT, DocumentStatus::VALIDATED);
            $dossier->addDocument($motivation);
            $dossier->addDocument($transcript);

            $this->candidateRepository->save($dossier, false);
            $this->timelineService->record($dossier, 'candidate.created', 'Dossier candidat créé');
            $this->timelineService->record($dossier, 'document.uploaded', 'Passeport ajouté');
            $this->timelineService->record($dossier, 'document.validated', 'CV validé');
            $this->timelineService->record($dossier, 'candidate.status_changed', 'Admission obtenue');
            $this->syncMohamedDevPortalState($dossier);
            $io->success('Dossier démo Mohamed Koffi créé.');
        } else {
            if (null === $dossier->getAssignedCounselor()) {
                $dossier->setAssignedCounselor($counselor);
            }
            if (null === $dossier->getStudyApplicationType()) {
                $dossier->setStudyApplicationType(StudyApplicationType::FIRST_YEAR);
            }
            $this->syncMohamedDevPortalState($dossier);
            $io->note('Dossier démo Mohamed Koffi existant — aligné sur l’état DEV.');
        }

        $this->seedContinuingDemoCandidate(
            $counselor,
            $candidateRole,
            $continuingUserEmail,
            $continuingUserPassword,
            $io,
        );

        $this->seedExtraCandidates($counselor);
        $this->entityManager->flush();

        $io->success('Utilisateurs et dossiers de démonstration prêts.');

        return Command::SUCCESS;
    }

    private function ensureDemoApplications(Candidate $dossier): void
    {
        if (StudyApplicationType::FIRST_YEAR === $dossier->getStudyApplicationType()) {
            if (null === $dossier->getParcoursupApplication()) {
                $parcoursup = new ParcoursupApplication();
                $parcoursup->setIneNumber('123456789AB');
                $parcoursup->setHighSchool('Lycée Moderne d\'Abidjan');
                $parcoursup->setMotivationProject('Projet d\'études en informatique et IA');
                $wish = new ParcoursupWish(1, 'Université Paris-Saclay', 'Licence Informatique', ParcoursupWishStatus::ACCEPTE);
                $wish->setSubmittedAt(new \DateTimeImmutable('-30 days'));
                $parcoursup->addWish($wish);
                $wish2 = new ParcoursupWish(2, 'Sorbonne Université', 'Licence Mathématiques', ParcoursupWishStatus::LISTE_ATTENTE);
                $wish2->setSubmittedAt(new \DateTimeImmutable('-28 days'));
                $parcoursup->addWish($wish2);
                $dossier->setParcoursupApplication($parcoursup);
            }
        }

        if (StudyApplicationType::CONTINUING === $dossier->getStudyApplicationType()) {
            if (null === $dossier->getParisSaclayApplication()) {
                $parisSaclay = new ParisSaclayApplication();
                $parisSaclay->setDegreeLevel(ParisSaclayDegreeLevel::MASTER);
                $parisSaclay->setStatus(ParisSaclayStatus::UNDER_REVIEW);
                $dossier->setParisSaclayApplication($parisSaclay);
            }
        }

        $campus = $dossier->getCampusFranceApplication();
        if ($campus instanceof CampusFranceApplication && CampusFranceStatus::DRAFT === $campus->getStatus()) {
            $campus->setStatus(CampusFranceStatus::ADMISSION_OBTAINED);
        }

        $parisSaclay = $dossier->getParisSaclayApplication();
        if ($parisSaclay instanceof ParisSaclayApplication && ParisSaclayStatus::DRAFT === $parisSaclay->getStatus()) {
            $parisSaclay->setStatus(ParisSaclayStatus::UNDER_REVIEW);
        }
    }

    /**
     * État portail candidat Mohamed = DEV : Parcoursup + Campus France (première année).
     */
    private function syncMohamedDevPortalState(Candidate $dossier): void
    {
        $this->ensureDemoApplications($dossier);
        $this->removeParisSaclayForFirstYear($dossier);
        $this->pathwayAssignmentService->reassignForCandidate($dossier);

        $counselor = $dossier->getAssignedCounselor();
        if ($counselor instanceof User) {
            $this->seedMohamedPathwayProgress($dossier, $counselor);
        }
    }

    private function removeParisSaclayForFirstYear(Candidate $dossier): void
    {
        if (StudyApplicationType::FIRST_YEAR !== $dossier->getStudyApplicationType()) {
            return;
        }

        if ($dossier->getParisSaclayApplication() instanceof ParisSaclayApplication) {
            $dossier->setParisSaclayApplication(null);
        }

        foreach ($dossier->getPathways()->toArray() as $pathway) {
            if (PathwayCode::PARIS_SACLAY === $pathway->getPathwayTemplate()->getCode()) {
                $dossier->removePathway($pathway);
                $this->entityManager->remove($pathway);
            }
        }
    }

    private function seedMohamedPathwayProgress(Candidate $dossier, User $counselor): void
    {
        $targets = [
            PathwayCode::CAMPUS_FRANCE->value => 67,
            PathwayCode::PARCOURSUP->value => 100,
        ];

        foreach ($dossier->getPathways() as $pathway) {
            $code = $pathway->getPathwayTemplate()->getCode()->value;
            $target = $targets[$code] ?? null;
            if (null === $target) {
                continue;
            }

            $this->resetPathwayDemoProgress($pathway);
            $this->seedPathwayProgressToTarget($pathway, $target, $counselor);
        }
    }

    private function resetPathwayDemoProgress(CandidatePathway $pathway): void
    {
        foreach ($pathway->getStages() as $stage) {
            foreach ($stage->getSubSteps() as $subStep) {
                $subStep->clearValidation();
            }
        }

        $this->pathwayProgressCalculator->refresh($pathway);
    }

    private function seedPathwayProgressToTarget(CandidatePathway $pathway, int $targetPercent, User $counselor): void
    {
        foreach ($pathway->getStages() as $stage) {
            foreach ($stage->getSubSteps() as $subStep) {
                if (!$subStep->getSubStepTemplate()->isRequired()) {
                    continue;
                }

                if ($subStep->isValidated(false)) {
                    continue;
                }

                $subStep->validateByCounselor($counselor);
                $subStep->setGrandfatheredValidation(true);
                $this->pathwayProgressCalculator->refresh($pathway);

                if ($pathway->getProgressPercent() >= $targetPercent) {
                    return;
                }
            }
        }
    }

    private function seedContinuingDemoCandidate(
        User $counselor,
        \App\Entity\Role $candidateRole,
        string $email,
        string $password,
        SymfonyStyle $io,
    ): void {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            $user = new User($email, 'Ama', 'Diallo');
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->addRole($candidateRole);
            $this->userRepository->save($user, false);
            $io->success(sprintf('Compte candidat poursuite créé : %s / %s', $email, $password));
        } else {
            $io->note(sprintf('Compte candidat poursuite existant : %s', $email));
        }

        $dossier = $this->candidateRepository->findOneBy(['email' => $email]);
        if (!$dossier) {
            $dossier = new Candidate('Ama', 'Diallo', $email, 'Sénégal');
            $dossier->setReferenceNumber($this->referenceGenerator->generate());
            $dossier->setStatus(CandidateStatus::IN_PROGRESS);
            $dossier->setPhone('+221 77 000 00 00');
            $dossier->setCity('Dakar');
            $dossier->setCountry('Sénégal');
            $dossier->setAssignedCounselor($counselor);
            $dossier->setStudyApplicationType(StudyApplicationType::CONTINUING);

            $passport = new CandidateDocument($dossier, DocumentType::PASSPORT, DocumentStatus::VALIDATED);
            $transcript = new CandidateDocument($dossier, DocumentType::TRANSCRIPT, DocumentStatus::UPLOADED);
            $dossier->addDocument($passport);
            $dossier->addDocument($transcript);

            $campusFrance = new CampusFranceApplication();
            $campusFrance->setStudyProject('Doctorat en énergies renouvelables');
            $campusFrance->setStatus(CampusFranceStatus::INTERVIEW_SCHEDULED);
            $dossier->setCampusFranceApplication($campusFrance);

            $parisSaclay = new ParisSaclayApplication();
            $parisSaclay->setDegreeLevel(ParisSaclayDegreeLevel::DOCTORATE);
            $parisSaclay->setResearchProject('Stockage d\'énergie solaire');
            $parisSaclay->setStatus(ParisSaclayStatus::UNDER_REVIEW);
            $dossier->setParisSaclayApplication($parisSaclay);

            $this->candidateRepository->save($dossier, false);
            $this->timelineService->record($dossier, 'candidate.created', 'Dossier candidat créé (poursuite d\'études)');
            $this->pathwayAssignmentService->assignForCandidate($dossier);
            $io->success('Dossier démo Ama Diallo (poursuite d\'études) créé.');
        } else {
            if (null === $dossier->getAssignedCounselor()) {
                $dossier->setAssignedCounselor($counselor);
            }
            if (StudyApplicationType::CONTINUING !== $dossier->getStudyApplicationType()) {
                $dossier->setStudyApplicationType(StudyApplicationType::CONTINUING);
            }
            $this->ensureDemoApplications($dossier);
            $this->pathwayAssignmentService->reassignForCandidate($dossier);
            $io->note('Dossier démo Ama Diallo existant — parcours Campus France + Paris-Saclay.');
        }
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
            $candidate->setStudyApplicationType(StudyApplicationType::CONTINUING);
            $this->candidateRepository->save($candidate);
            $this->pathwayAssignmentService->assignForCandidate($candidate);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Domain\User\Enum\SystemRole;
use App\Entity\Candidate;
use App\Entity\User;
use App\Infrastructure\Pathway\PathwayAssignmentService;
use App\Repository\CandidateRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class CandidateOnboardingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private CandidateRepository $candidateRepository,
        private RoleRepository $roleRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private CandidateReferenceGenerator $referenceGenerator,
        private CandidateTimelineService $timelineService,
        private PathwayAssignmentService $pathwayAssignmentService,
    ) {
    }

    public function register(CandidateRegistrationData $data): User
    {
        $email = strtolower(trim($data->email));

        if ($this->userRepository->findByEmail($email)) {
            throw new BadRequestHttpException('Cet email est déjà utilisé.');
        }

        if ($this->candidateRepository->findOneBy(['email' => $email])) {
            throw new BadRequestHttpException('Un dossier candidat existe déjà pour cet email.');
        }

        $candidateRole = $this->roleRepository->findByCode(SystemRole::CANDIDATE->value);
        if (!$candidateRole) {
            throw new BadRequestHttpException('Le rôle candidat n\'est pas configuré.');
        }

        return $this->entityManager->wrapInTransaction(function () use ($data, $email, $candidateRole): User {
            $user = new User($email, $data->firstName, $data->lastName);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data->password));
            $user->addRole($candidateRole);
            $this->userRepository->save($user, false);

            $candidate = new Candidate($data->firstName, $data->lastName, $email, $data->nationality);
            $candidate->setReferenceNumber($this->referenceGenerator->generate());
            $candidate->setStudyApplicationType($data->studyApplicationType);

            if (null !== $data->phone && '' !== trim($data->phone)) {
                $candidate->setPhone(trim($data->phone));
            }
            if (null !== $data->city && '' !== trim($data->city)) {
                $candidate->setCity(trim($data->city));
            }
            if (null !== $data->country && '' !== trim($data->country)) {
                $candidate->setCountry(trim($data->country));
            }

            $this->candidateRepository->save($candidate, false);
            $this->timelineService->record($candidate, 'candidate.created', 'Inscription candidat — dossier créé');
            $this->pathwayAssignmentService->assignForCandidate($candidate);

            $this->entityManager->flush();

            return $user;
        });
    }
}

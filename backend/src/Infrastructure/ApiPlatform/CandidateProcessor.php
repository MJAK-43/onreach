<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Candidate\Enum\CandidateStatus;
use App\Entity\Candidate;
use App\Entity\User;
use App\Infrastructure\Candidate\CandidateReferenceGenerator;
use App\Infrastructure\Candidate\CandidateTimelineService;
use App\Repository\CandidateRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<Candidate, Candidate>
 */
final readonly class CandidateProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Candidate, Candidate> $persistProcessor
     */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private CandidateRepository $candidateRepository,
        private CandidateReferenceGenerator $referenceGenerator,
        private CandidateTimelineService $timelineService,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Candidate
    {
        /** @var Candidate $data */
        $method = $operation instanceof HttpOperation ? strtolower($operation->getMethod()) : '';

        if ('post' === $method) {
            if ($this->candidateRepository->findOneBy(['email' => $data->getEmail()])) {
                throw new BadRequestHttpException('Un candidat avec cet email existe déjà.');
            }
            $data->setReferenceNumber($this->referenceGenerator->generate());

            $user = $this->security->getUser();
            if ($user instanceof User && $user->hasRole('COUNSELOR') && null === $data->getAssignedCounselor()) {
                $data->setAssignedCounselor($user);
            }
        }

        $previousStatus = null;
        if ('put' === $method && isset($uriVariables['id'])) {
            $existing = $this->candidateRepository->find($uriVariables['id']);
            if ($existing instanceof Candidate) {
                $previousStatus = $existing->getStatus();
            }
        }

        /** @var Candidate $result */
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        if ('post' === $method) {
            $this->timelineService->record($result, 'candidate.created', 'Dossier candidat créé');
        }

        if ('put' === $method && $previousStatus instanceof CandidateStatus && $previousStatus !== $result->getStatus()) {
            $this->timelineService->record(
                $result,
                'candidate.status_changed',
                sprintf('Statut changé : %s → %s', $previousStatus->label(), $result->getStatus()->label()),
                ['from' => $previousStatus->value, 'to' => $result->getStatus()->value],
            );
        }

        return $result;
    }
}

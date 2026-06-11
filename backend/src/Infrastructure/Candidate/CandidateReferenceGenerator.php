<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Repository\CandidateRepository;

final readonly class CandidateReferenceGenerator
{
    public function __construct(
        private CandidateRepository $candidateRepository,
    ) {
    }

    public function generate(): string
    {
        $year = (int) date('Y');
        $sequence = $this->candidateRepository->countForYear($year) + 1;

        return sprintf('ONR-%d-%05d', $year, $sequence);
    }
}

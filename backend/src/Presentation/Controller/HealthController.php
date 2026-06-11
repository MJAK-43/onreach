<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Health\Query\GetHealthQuery;
use App\Application\Health\Query\GetHealthQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function __invoke(GetHealthQueryHandler $handler): JsonResponse
    {
        $status = $handler(new GetHealthQuery());

        return new JsonResponse($status->toArray());
    }
}

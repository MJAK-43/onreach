<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Auth\Query\GetCurrentUserQuery;
use App\Application\Auth\Query\GetCurrentUserQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(GetCurrentUserQueryHandler $handler): JsonResponse
    {
        return new JsonResponse($handler(new GetCurrentUserQuery()));
    }
}

<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Pathway\PathwayCalendarImportService;
use App\Infrastructure\Pathway\PathwayTemplateAdminService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminPathwayAdminController extends AbstractController
{
    public function __construct(
        private readonly PathwayTemplateAdminService $adminService,
        private readonly PathwayCalendarImportService $calendarImportService,
    ) {
    }

    #[Route('/campaigns', name: 'admin_campaigns_list', methods: ['GET'])]
    public function listCampaigns(): JsonResponse
    {
        return new JsonResponse(['items' => $this->adminService->listCampaigns()]);
    }

    #[Route('/campaigns', name: 'admin_campaigns_create', methods: ['POST'])]
    public function createCampaign(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        return new JsonResponse($this->adminService->createCampaign($payload), 201);
    }

    #[Route('/campaigns/{id}', name: 'admin_campaigns_patch', methods: ['PATCH'])]
    public function patchCampaign(string $id, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        return new JsonResponse($this->adminService->patchCampaign($id, $payload));
    }

    #[Route('/pathway-templates', name: 'admin_pathway_templates_list', methods: ['GET'])]
    public function listTemplates(Request $request): JsonResponse
    {
        return new JsonResponse([
            'items' => $this->adminService->listTemplates($request->query->get('campaign')),
        ]);
    }

    #[Route('/pathway-templates/{id}', name: 'admin_pathway_templates_show', methods: ['GET'])]
    public function showTemplate(string $id): JsonResponse
    {
        return new JsonResponse($this->adminService->getTemplate($id));
    }

    #[Route('/pathway-templates/{id}', name: 'admin_pathway_templates_patch', methods: ['PATCH'])]
    public function patchTemplate(string $id, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        return new JsonResponse($this->adminService->patchTemplate($id, $payload));
    }

    #[Route('/pathway-stages/{id}', name: 'admin_pathway_stages_patch', methods: ['PATCH'])]
    public function patchStage(string $id, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        return new JsonResponse($this->adminService->patchStage($id, $payload));
    }

    #[Route('/pathway-sub-steps/{id}', name: 'admin_pathway_sub_steps_patch', methods: ['PATCH'])]
    public function patchSubStep(string $id, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        return new JsonResponse($this->adminService->patchSubStep($id, $payload));
    }

    #[Route('/pathways/import-calendar', name: 'admin_pathways_import_calendar', methods: ['POST'])]
    public function importCalendar(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload) || empty($payload['templateId']) || empty($payload['calendarText'])) {
            throw new BadRequestHttpException('templateId et calendarText sont requis.');
        }

        return new JsonResponse($this->calendarImportService->import(
            (string) $payload['templateId'],
            (string) $payload['calendarText'],
            (bool) ($payload['apply'] ?? false),
        ));
    }
}

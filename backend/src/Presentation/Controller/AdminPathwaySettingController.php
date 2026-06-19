<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Repository\PathwaySettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/pathway-settings')]
#[IsGranted('ROLE_ADMIN')]
final class AdminPathwaySettingController extends AbstractController
{
    public function __construct(
        private readonly PathwaySettingRepository $settingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'admin_pathway_settings_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $settings = $this->settingRepository->findAllOrdered();

        return new JsonResponse([
            'items' => array_map(static fn ($setting) => [
                'code' => $setting->getPathwayCode()->value,
                'label' => $setting->getPathwayCode()->label(),
                'doubleValidationEnabled' => $setting->isDoubleValidationEnabled(),
            ], $settings),
        ]);
    }

    #[Route('/{code}', name: 'admin_pathway_settings_patch', methods: ['PATCH'])]
    public function patch(string $code, Request $request): JsonResponse
    {
        $pathwayCode = PathwayCode::tryFrom($code);
        if (!$pathwayCode) {
            throw new NotFoundHttpException('Parcours introuvable.');
        }

        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload) || !\array_key_exists('doubleValidationEnabled', $payload)) {
            throw new BadRequestHttpException('Le champ doubleValidationEnabled est requis.');
        }

        $setting = $this->settingRepository->findOneByPathwayCode($pathwayCode);
        if (!$setting) {
            throw new NotFoundHttpException('Paramètre introuvable.');
        }

        $setting->setDoubleValidationEnabled((bool) $payload['doubleValidationEnabled']);
        $this->entityManager->flush();

        return new JsonResponse([
            'code' => $setting->getPathwayCode()->value,
            'label' => $setting->getPathwayCode()->label(),
            'doubleValidationEnabled' => $setting->isDoubleValidationEnabled(),
        ]);
    }
}

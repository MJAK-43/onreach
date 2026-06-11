<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Security\SecurityEventType;
use App\Entity\Permission;
use App\Infrastructure\Security\SecurityLogService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<Permission, Permission>
 */
final readonly class PermissionProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Permission, Permission> $persistProcessor
     */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private SecurityLogService $securityLogService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Permission
    {
        /** @var Permission $data */
        if ($data->isSystem() && '' === trim($data->getName())) {
            throw new BadRequestHttpException('Le libellé d\'une permission système ne peut pas être vide.');
        }

        /** @var Permission $result */
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        $method = $operation instanceof HttpOperation ? strtolower($operation->getMethod()) : '';
        if ('patch' === $method) {
            $this->securityLogService->log(
                SecurityEventType::PERMISSION_UPDATED,
                metadata: ['permissionCode' => $result->getCode()],
            );
        }

        return $result;
    }
}

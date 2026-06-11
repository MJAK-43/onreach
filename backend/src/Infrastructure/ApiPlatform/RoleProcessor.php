<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Security\SecurityEventType;
use App\Entity\Role;
use App\Infrastructure\Security\SecurityLogService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<Role, Role>
 */
final readonly class RoleProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Role, Role> $persistProcessor
     */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private SecurityLogService $securityLogService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Role
    {
        /** @var Role $data */
        if ($data->isSystem() && $data->getPermissions()->isEmpty()) {
            throw new BadRequestHttpException('Un rôle système doit conserver au moins une permission.');
        }

        /** @var Role $result */
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        $method = $operation instanceof HttpOperation ? strtolower($operation->getMethod()) : '';
        if ('patch' === $method) {
            $this->securityLogService->log(
                SecurityEventType::ROLE_UPDATED,
                metadata: ['roleCode' => $result->getCode()],
            );
        }

        return $result;
    }
}

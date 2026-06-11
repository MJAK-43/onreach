<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Security\PasswordPolicy;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<User, User>
 */
final readonly class UserProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<User, User> $persistProcessor
     */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        /** @var User $data */
        $request = $context['request'] ?? null;
        $requestData = [];
        if ($request instanceof \Symfony\Component\HttpFoundation\Request) {
            $requestData = json_decode($request->getContent(), true) ?? [];
        }
        $plainPassword = $requestData['plainPassword'] ?? null;
        $method = $operation instanceof HttpOperation ? strtolower($operation->getMethod()) : '';

        if ('post' === $method) {
            if (!is_string($plainPassword) || '' === $plainPassword) {
                throw new BadRequestHttpException('Le mot de passe est requis.');
            }
            $errors = PasswordPolicy::validate($plainPassword);
            if ([] !== $errors) {
                throw new BadRequestHttpException(implode(' ', $errors));
            }
            if ($this->userRepository->findByEmail($data->getEmail())) {
                throw new BadRequestHttpException('Cet email est déjà utilisé.');
            }
            $data->setPassword($this->passwordHasher->hashPassword($data, $plainPassword));
        }

        if (is_string($plainPassword) && '' !== $plainPassword && 'patch' === $method) {
            $errors = PasswordPolicy::validate($plainPassword);
            if ([] !== $errors) {
                throw new BadRequestHttpException(implode(' ', $errors));
            }
            $data->setPassword($this->passwordHasher->hashPassword($data, $plainPassword));
        }

        /** @var User $result */
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        return $result;
    }
}

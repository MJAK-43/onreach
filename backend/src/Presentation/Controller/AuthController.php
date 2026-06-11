<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Auth\Command\ChangePasswordCommand;
use App\Application\Auth\Command\ChangePasswordCommandHandler;
use App\Application\Auth\Command\ForgotPasswordCommand;
use App\Application\Auth\Command\ForgotPasswordCommandHandler;
use App\Application\Auth\Command\LoginCommand;
use App\Application\Auth\Command\LoginCommandHandler;
use App\Application\Auth\Command\MfaDisableCommandHandler;
use App\Application\Auth\Command\MfaEnableCommandHandler;
use App\Application\Auth\Command\MfaSetupCommandHandler;
use App\Application\Auth\Command\ResetPasswordCommand;
use App\Application\Auth\Command\ResetPasswordCommandHandler;
use App\Application\Auth\DTO\LoginRequest;
use App\Domain\Security\SecurityEventType;
use App\Entity\User;
use App\Infrastructure\Security\SecurityLogService;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/auth')]
final class AuthController extends AbstractController
{
    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] LoginRequest $loginRequest,
        Request $request,
        LoginCommandHandler $handler,
    ): JsonResponse {
        $result = $handler(new LoginCommand(
            email: $loginRequest->email,
            password: $loginRequest->password,
            rememberMe: $loginRequest->rememberMe,
            mfaCode: $loginRequest->mfaCode,
            ip: $request->getClientIp(),
        ));

        if (($result['requiresMfa'] ?? false) === true) {
            return new JsonResponse(['requiresMfa' => true], Response::HTTP_OK);
        }

        return new JsonResponse($result);
    }

    #[Route('/logout', name: 'auth_logout', methods: ['POST'])]
    public function logout(
        Request $request,
        RefreshTokenManagerInterface $refreshTokenManager,
        SecurityLogService $securityLogService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $refreshToken = $data['refreshToken'] ?? null;
        if (is_string($refreshToken) && '' !== $refreshToken) {
            $token = $refreshTokenManager->get($refreshToken);
            if (null !== $token) {
                $refreshTokenManager->delete($token);
            }
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $securityLogService->log(SecurityEventType::LOGOUT, $user);
        }

        return new JsonResponse(['message' => 'Déconnexion réussie.']);
    }

    #[Route('/forgot-password', name: 'auth_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request, ForgotPasswordCommandHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = (string) ($data['email'] ?? '');

        return new JsonResponse($handler(new ForgotPasswordCommand($email)));
    }

    #[Route('/reset-password', name: 'auth_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, ResetPasswordCommandHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $result = $handler(new ResetPasswordCommand(
            token: (string) ($data['token'] ?? ''),
            password: (string) ($data['password'] ?? ''),
        ));

        return new JsonResponse($result);
    }

    #[Route('/change-password', name: 'auth_change_password', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function changePassword(Request $request, ChangePasswordCommandHandler $handler): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];
        $result = $handler(new ChangePasswordCommand(
            user: $user,
            currentPassword: (string) ($data['currentPassword'] ?? ''),
            newPassword: (string) ($data['newPassword'] ?? ''),
        ));

        return new JsonResponse($result);
    }

    #[Route('/mfa/setup', name: 'auth_mfa_setup', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function mfaSetup(MfaSetupCommandHandler $handler): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse($handler($user));
    }

    #[Route('/mfa/enable', name: 'auth_mfa_enable', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function mfaEnable(Request $request, MfaEnableCommandHandler $handler): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        return new JsonResponse($handler($user, (string) ($data['code'] ?? '')));
    }

    #[Route('/mfa/disable', name: 'auth_mfa_disable', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function mfaDisable(Request $request, MfaDisableCommandHandler $handler): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        return new JsonResponse($handler(
            $user,
            (string) ($data['password'] ?? ''),
            (string) ($data['code'] ?? ''),
        ));
    }
}

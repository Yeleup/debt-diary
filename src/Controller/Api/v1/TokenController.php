<?php
namespace App\Controller\Api\v1;

use App\Repository\UserRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/token')]
class TokenController extends AbstractController
{
    public function __construct(private readonly AuthService $authService, protected UserRepository $userRepository)
    {
    }

    #[Route(path: '', methods: ['POST'])]
    public function getTokenAction(Request $request): Response
    {
        $userIdentifier = $request->getUser();
        $password = $request->getPassword();
        if (!$userIdentifier || !$password) {
            return new JsonResponse(['message' => 'Authorization required'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->authService->isCredentialsValid($userIdentifier, $password)) {
            return new JsonResponse(['message' => 'Invalid password or username'], Response::HTTP_FORBIDDEN);
        }

        $user = $this->userRepository->findOneBy(['username' => $userIdentifier]);
        return new JsonResponse([
            'username' => $user->getUsername(),
            'fullName' => $user->getFullName(),
            'token' => $this->authService->getToken($userIdentifier),
        ]);
    }
}

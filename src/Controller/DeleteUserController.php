<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteUserController extends AbstractController
{
    public function __construct(private UserService $userService, private UserRepository $userRepository)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'];

        $user = $this->userRepository->find($userId);

        $this->userService->handleDeletionRequest($user);

        return new JsonResponse([
            'message' => 'User deleted or anonymized successfully',
        ], Response::HTTP_OK);
    }
}

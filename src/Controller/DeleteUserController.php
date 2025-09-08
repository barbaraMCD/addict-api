<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteUserController extends AbstractController
{
    public function __construct(private UserService $userService)
    {
    }

    public function __invoke(User $user): JsonResponse
    {
        try {
            $this->userService->handleDeletionRequest($user);
            return new JsonResponse(['message' => 'User deleted or anonymized successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

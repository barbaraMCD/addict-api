<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserPasswordHasherInterface $passwordHasher,
        private SubscriptionRepository $subscriptionRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function registerUser(array $userData): User
    {

        $user = new User();
        $user->setEmail($userData['email']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
        $user->setPassword($hashedPassword);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    public function handleDeletionRequest(User $user): void
    {
        $subscription = $this->subscriptionRepository->findOneBy([
            'user' => $user,
        ]);

        if ($subscription) {
            $this->anonymizeUser($user);
        } else {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();
    }

    private function anonymizeUser(User $user): void
    {
        $user->setEmail('deleted-' . uniqid() . '@anonymous.local');
        $user->setPassword('');
        $user->setUsername('deleted-' . uniqid());
    }

}

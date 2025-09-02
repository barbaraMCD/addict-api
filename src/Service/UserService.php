<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(private EntityManagerInterface $manager, private UserPasswordHasherInterface $passwordHasher, private UserRepository $userRepository)
    {
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

}

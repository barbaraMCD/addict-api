<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends BaseFixtures
{
    public const COUNT_NB_USER = 5;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT_NB_USER; ++$i) {
            $user = new User();
            $user->setEmail('user'.$i.'@test.local');
            $user->setUsername($this->faker->userName());

            $hashedPassword = $this->passwordHasher->hashPassword($user, "test");
            $user->setPassword($hashedPassword);

            $this->addReference('USER_'.$i, $user);
            $manager->persist($user);
        }

        $manager->flush();
    }
}

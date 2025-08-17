<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends BaseFixtures
{
    public const COUNT_NB_USER = 5;

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT_NB_USER; ++$i) {
            $user = new User();
            $user->setEmail('user'.$i.'@test.local');
            $user->setUsername($this->faker->userName());
            $user->setPassword($this->faker->password());

            $this->addReference('USER_'.$i, $user);
            $manager->persist($user);
        }

        $manager->flush();
    }
}

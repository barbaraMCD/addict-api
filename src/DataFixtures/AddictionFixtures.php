<?php

namespace App\DataFixtures;

use App\Entity\Addiction;
use App\Enum\Addiction\AddictionType;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AddictionFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {

        foreach (AddictionType::cases() as $i => $case) {
            $addiction = new Addiction();
            $addiction->setType($case);

            $user = $this->getReference('USER_' . rand(1, UserFixtures::COUNT_NB_USER));
            $addiction->setUser($user);

            $this->addReference('ADDICTION_'.$i, $addiction);
            $manager->persist($addiction);
        }

        $manager->flush();
    }
}

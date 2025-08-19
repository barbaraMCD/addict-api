<?php

namespace App\DataFixtures;

use App\Entity\Addiction;
use App\Enum\AddictionEnumType;
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
        $users = [
            $this->getReference('USER_1'),
            $this->getReference('USER_2'),
            $this->getReference('USER_3'),
        ];

        foreach (AddictionEnumType::cases() as $i => $case) {
            $addiction = new Addiction();
            $addiction->setType($case->name);
            $addiction->setUser($users[$i % count($users)]);

            $this->addReference('ADDICTION_'.$i, $addiction);
            $manager->persist($addiction);
        }

        $manager->flush();
    }
}

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
            TriggerFixtures::class,
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
            $addiction->setName($case->name);
            $addiction->setUser($users[$i % count($users)]);

            // Ajouter 1 ou plusieurs triggers pour certaines addictions
            if (in_array($i, [4, 5, 6])) {
                $addiction->addTrigger($this->getReference('TRIGGER_0'));
                $addiction->addTrigger($this->getReference('TRIGGER_1'));
            } else {
                $addiction->addTrigger($this->getReference('TRIGGER_0'));
            }

            $manager->persist($addiction);
        }

        $manager->flush();
    }
}

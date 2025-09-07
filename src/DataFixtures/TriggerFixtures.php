<?php

namespace App\DataFixtures;

use App\Entity\Trigger;
use App\Enum\Trigger\TriggerType;
use Doctrine\Persistence\ObjectManager;

class TriggerFixtures extends BaseFixtures
{
    public function load(ObjectManager $manager): void
    {
        foreach (TriggerType::cases() as $i => $case) {
            $trigger = new Trigger();
            $trigger->setType($case);

            $this->addReference('TRIGGER_'.$i, $trigger);
            $manager->persist($trigger);
        }

        $manager->flush();
    }
}

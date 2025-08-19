<?php

namespace App\DataFixtures;

use App\Entity\Consumption;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConsumptionFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public const COUNT_NB_CONSUMPTIONS = 10;
    public function getDependencies(): array
    {
        return [
            AddictionFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {

        $addictions = [
            $this->getReference('ADDICTION_1'),
            $this->getReference('ADDICTION_2'),
            $this->getReference('ADDICTION_3'),
        ];

        for ($i = 1; $i <= self::COUNT_NB_CONSUMPTIONS; ++$i) {
            $consumption = new Consumption();
            $consumption->setAddiction($addictions[$i % count($addictions)]);
            $consumption->setDate($this->faker->dateTime());

            // Add triggers for some consumptions
            if (in_array($i, [4, 5, 6])) {
                $consumption->addTrigger($this->getReference('TRIGGER_3'));
                $consumption->addTrigger($this->getReference('TRIGGER_1'));
            } else {
                $consumption->addTrigger($this->getReference('TRIGGER_0'));
            }


            $this->addReference('CONSUMPTION_'.$i, $consumption);
            $manager->persist($consumption);
        }

        $manager->flush();
    }
}

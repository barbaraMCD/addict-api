<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use App\Enum\Subscription\PlanType;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SubscriptionFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public const COUNT_NB_SUB = 5;

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT_NB_SUB; ++$i) {
            $subscription = new Subscription();

            $user = $this->getReference('USER_' . rand(1, UserFixtures::COUNT_NB_USER));
            $subscription->setUser($user);

            $subscription->setPlanType(PlanType::MONTHLY);

            $subscription->setStripeCustomerId('cus_' . $user->getId());
            $subscription->setStripeSubscriptionId('sub_' . $this->faker->bothify('??########'));

            $startDate = $this->faker->dateTimeBetween('-6 months', '+1 month');
            $endDate = clone $startDate;
            $endDate->modify('+1 month');

            $subscription->setCurrentPeriodStart(\DateTimeImmutable::createFromMutable($startDate));
            $subscription->setCurrentPeriodEnd(\DateTimeImmutable::createFromMutable($endDate));

            $this->addReference('SUBSCRIPTION_'.$i, $subscription);
            $manager->persist($subscription);
        }

        $manager->flush();
    }
}

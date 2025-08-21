<?php

namespace App\EventSubscriber;

use App\Entity\Consumption;
use App\Service\ConsumptionService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;

#[AsDoctrineListener(event: Events::prePersist)]
class ConsumptionEventSubscriber
{
    public function __construct(private ConsumptionService $consumptionService)
    {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $consumption = $args->getObject();

        if (!$consumption instanceof Consumption) {
            return;
        }

        $existingConsumptions = $this->consumptionService->getConsumptionsForTheDay($consumption);

        if (!empty($existingConsumptions)) {
            $existing = $existingConsumptions[0];

            $existing->setQuantity($existing->getQuantity() + $consumption->getQuantity());
            if ($consumption->getComment()) {
                $existing->setComment($consumption->getComment());
            }

            $em = $args->getObjectManager();
            $em->detach($consumption);

            $em->persist($existing);
        }
    }

}

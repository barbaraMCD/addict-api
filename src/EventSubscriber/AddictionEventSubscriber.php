<?php

namespace App\EventSubscriber;

use App\Entity\Addiction;
use App\Service\AddictionService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

#[AsDoctrineListener(event: Events::prePersist)]
class AddictionEventSubscriber
{
    public function __construct(private AddictionService $addictionService)
    {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $addiction = $args->getObject();

        if (!$addiction instanceof Addiction) {
            return;
        }

        $existingAddiction = $this->addictionService->checkIfAddictionForUserAlreadyExists($addiction);

        if ($existingAddiction) {
            throw new ConflictHttpException('Une addiction de ce type existe déjà pour cet utilisateur.');
        }
    }

}

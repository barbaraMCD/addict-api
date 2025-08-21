<?php

namespace App\Service;

namespace App\Service;

use App\Entity\Addiction;
use App\Repository\AddictionRepository;

class AddictionService
{
    public function __construct(
        private AddictionRepository $addictionRepository,
    ) {
    }

    public function checkIfAddictionForUserAlreadyExists(Addiction $addiction): bool
    {
        return (bool)$this->addictionRepository->findOneBy([
            'user' => $addiction->getUser(),
            'type' => $addiction->getType(),
        ]);
    }
}

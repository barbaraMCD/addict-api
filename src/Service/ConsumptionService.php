<?php

namespace App\Service;

use App\Entity\Consumption;
use App\Repository\ConsumptionRepository;

class ConsumptionService
{
    public function __construct(
        private ConsumptionRepository $consumptionRepository,
    ) {
    }

    public function getConsumptionsForTheDay(Consumption $consumption): array
    {

        return $this->consumptionRepository->findByAddictionUserAndDate(
            $consumption->getAddiction(),
            $consumption->getAddiction()->getUser(),
            $consumption->getDate()
        );
    }
}

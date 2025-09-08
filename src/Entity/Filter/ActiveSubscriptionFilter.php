<?php

namespace App\Entity\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class ActiveSubscriptionFilter extends AbstractFilter
{
    protected function filterProperty(
        string                      $property,
        $value,
        QueryBuilder                $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string                      $resourceClass,
        Operation                   $operation = null,
        array                       $context = []
    ): void {
        if ($property !== 'isActive') {
            return;
        }

        $now = new \DateTime();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($value === 'true' || $value === '1') {
            $queryBuilder
                ->andWhere(sprintf('%s.currentPeriodStart <= :now', $rootAlias))
                ->andWhere(sprintf('%s.currentPeriodEnd >= :now', $rootAlias))
                ->setParameter('now', $now);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'isActive' => [
                'property' => 'isActive',
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}

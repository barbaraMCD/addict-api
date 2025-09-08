<?php

namespace App\Entity\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class NonAnonymizedUserFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if ($property !== 'activeUsers') {
            return;
        }

        if ($value === 'true') {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->innerJoin(sprintf('%s.user', $rootAlias), 'u')
                ->andWhere('u.email NOT LIKE :anonymizedPattern')
                ->setParameter('anonymizedPattern', 'deleted-%@anonymous.local');
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'activeUsers' => [
                'property' => 'activeUsers',
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}

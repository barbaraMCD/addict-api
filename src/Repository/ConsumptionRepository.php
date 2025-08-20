<?php

namespace App\Repository;

use App\Entity\Addiction;
use App\Entity\Consumption;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consumption>
 *
 * @method Consumption|null find($id, $lockMode = null, $lockVersion = null)
 * @method Consumption|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consumption[]    findAll()
 * @method Consumption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsumptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consumption::class);
    }

    public function findByAddictionUserAndDate(Addiction $addiction, User $user, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.addiction', 'a')
            ->andWhere('c.addiction = :addiction')
            ->andWhere('a.user = :user')
            ->andWhere('c.date BETWEEN :start AND :end')
            ->setParameters([
                'addiction' => $addiction,
                'user' => $user,
                'start' => (clone $date)->setTime(0, 0),
                'end' => (clone $date)->setTime(23, 59, 59),
            ])
            ->getQuery()
            ->getResult();
    }



}

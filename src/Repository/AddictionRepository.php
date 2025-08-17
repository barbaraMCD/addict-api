<?php

namespace App\Repository;

use App\Entity\Addiction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Addiction>
 *
 * @method Addiction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Addiction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Addiction[]    findAll()
 * @method Addiction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddictionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Addiction::class);
    }

    //    /**
    //     * @return Addiction[] Returns an array of Addiction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Addiction
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

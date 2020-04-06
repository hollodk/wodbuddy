<?php

namespace App\Repository;

use App\Entity\Wod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Wod|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wod|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wod[]    findAll()
 * @method Wod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wod::class);
    }

    // /**
    //  * @return Wod[] Returns an array of Wod objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Wod
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

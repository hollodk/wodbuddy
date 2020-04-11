<?php

namespace App\Repository;

use App\Entity\UserOrganization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserOrganization|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserOrganization|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserOrganization[]    findAll()
 * @method UserOrganization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserOrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOrganization::class);
    }

    // /**
    //  * @return UserOrganization[] Returns an array of UserOrganization objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserOrganization
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

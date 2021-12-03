<?php

namespace App\Repository;

use App\Entity\Mailer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mailer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mailer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mailer[]    findAll()
 * @method Mailer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mailer::class);
    }

    // /**
    //  * @return Mailer[] Returns an array of Mailer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mailer
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

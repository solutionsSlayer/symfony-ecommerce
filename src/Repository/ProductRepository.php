<?php

namespace App\Repository;

use App\Classe\Search;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Get products with search criteria
     */
    public function findWithSearch(Search $search): array
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('c','p')
            ->join('p.category', 'c');

        if (!empty($search->categories)) {
            $query = $query
                ->andWhere('c.id IN (:category)')
                ->setParameter('category', $search->categories);
        }

        // THE PERCENT SIGN IS FOR MAKE SOME PARTIAL RESEARCH
        if (!empty($search->string)) {
            $query = $query
                ->andWhere('p.name LIKE (:string)')
                ->setParameter('string', "%$search->string%");
        }

        return $query->getQuery()->getResult();
    }

    public function findBestProducts()
    {
         return $this->createQueryBuilder('p')
            ->where('p.isBest = 1')
             ->getQuery()
             ->getResult();
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

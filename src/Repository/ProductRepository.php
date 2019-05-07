<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private $repository;

    public function __construct(RegistryInterface $registry, AbstractRepository $repository)
    {
        parent::__construct($registry, Product::class);
        $this->repository = $repository;
    }

    public function search($term, $order = 'asc', $limit = 20, $offset = 0)
    {
        $queryBuilder = $this
            ->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.name', $order)
            ;

        if ($term)
        {
            $queryBuilder
                ->where('a.title LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
                ;
        }

        return $this->repository->paginate($queryBuilder, $limit, $offset);
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

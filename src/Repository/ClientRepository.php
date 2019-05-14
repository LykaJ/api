<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    private $repository;

    public function __construct(RegistryInterface $registry, AbstractRepository $repository)
    {
        parent::__construct($registry, Client::class);
        $this->repository = $repository;
    }

    public function search($term, $order = 'asc', $limit = 20, $offset = 0)
    {
        $queryBuilder = $this
            ->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.name', $order)
        ;

        if ($term)
        {
            $queryBuilder
                ->where('a.name LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
            ;
        }

        return $this->repository->paginate($queryBuilder, $limit, $offset);
    }

    // /**
    //  * @return Client[] Returns an array of Client objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Client
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

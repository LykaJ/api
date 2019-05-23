<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $repository;

    public function __construct(RegistryInterface $registry, AbstractRepository $repository)
    {
        parent::__construct($registry, User::class);
        $this->repository = $repository;
    }

    public function search($term, $order = 'asc', $limit = 20, $offset = 0)
    {
        $queryBuilder = $this
            ->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.username', $order)
        ;

        if ($term)
        {
            $queryBuilder
                ->where('u.username LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
            ;
        }

        return $this->repository->paginate($queryBuilder, $limit, $offset);
    }

    // /**
    //  * @return User[] Returns an array of User objects
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
    public function findOneBySomeField($value): ?User
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

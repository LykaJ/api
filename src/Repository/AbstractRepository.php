<?php
/**
 * Created by PhpStorm.
 * User: Alicia
 * Date: 2019-05-07
 * Time: 11:28
 */

namespace App\Repository;


use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function paginate(QueryBuilder $queryBuilder, $limit = 20, $offset = 0)
    {
        if ($limit >= 0 && $offset < 0)
        {
            throw new \LogicException('$limit and $offset must be greater than 0 : limit = ' .  $limit . ' offset = ' . $offset);
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($queryBuilder));
        $currentPage = ceil(($offset + 1) / $limit);
        $pager->setMaxPerPage((int) $limit);
        $pager->setCurrentPage($currentPage);


        return $pager;
    }
}
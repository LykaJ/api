<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CustomerController extends AbstractController
{
    private $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Rest\Get(
     *     path="customers",
     *     name="customer.list"
     * )
     *
     * @Rest\View(StatusCode=200)
     *
     * @param User $user
     * @return mixed
     */
    public function listAction(Security $security)
    {
        $user = $security->getUser();
        $customers = $this->repository->findByUser($user);

        return $customers;
    }
}

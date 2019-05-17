<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends AbstractController
{
    private $repository;
    private $userRepo;

    public function __construct(CustomerRepository $repository, UserRepository $userRepo)
    {
        $this->repository = $repository;
        $this->userRepo = $userRepo;
    }

    /**
     * @Rest\Get(
     *     path="api/customers/{id}",
     *     name="customers"
     * )
     *
     * @Rest\View(statusCode=200)
     *
     * @return \App\Entity\Customer[]
     */
    public function listAction(Request $request)
    {
        $user = $this->userRepo->findOneBy(['id' => $request->attributes->get('id')]);
        $customers = $this->repository->findByUser($user);

        return $customers;
    }
}

<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

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
     *
     * @Rest\Get(
     *     path="api/customers/",
     *     name="customers"
     * )
     *
     * @Rest\View(statusCode=200)
     *
     * @param Security $security
     * @return mixed|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function listAction(Security $security)
    {
        $user = $security->getToken()->getUser();
        $customers = $this->repository->findByUser($user);

        if (!$customers) {
            return $this->createNotFoundException('This user has no customer');
        }


        return $customers;
    }
}

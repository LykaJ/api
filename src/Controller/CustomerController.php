<?php

namespace App\Controller;

use App\Entity\Customer;
use App\EventSubscriber\ExceptionListener;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\ConstraintViolationList;

class CustomerController extends AbstractController
{
    private $repository;
    private $userRepo;
    private $manager;

    public function __construct(CustomerRepository $repository, UserRepository $userRepo, ObjectManager $manager)
    {
        $this->repository = $repository;
        $this->userRepo = $userRepo;
        $this->manager = $manager;
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

    /**
     * @Rest\Get(
     *     path="api/customer/{id}",
     *     name="customer.show",
     *     requirements={"id"="\d+"}
     * )
     *
     * @Rest\View(statusCode=200)
     *
     * @param Customer $customer
     * @return Customer
     */
    public function show(Customer $customer)
    {
        return $customer;
    }

    /**
     * @Rest\Post(
     *     path="api/customer",
     *     name="customer.create"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("customer", converter="fos_rest.request_body")
     *
     * @param Customer $customer
     * @param ConstraintViolationList $violations
     * @param ExceptionListener $listener
     * @return View
     * @throws \App\Exception\ResourceValidationException
     */
    public function create(Customer $customer, Security $security, ConstraintViolationList $violations, ExceptionListener $listener)
    {
        $listener->getViolations($violations);

        $user = $security->getToken()->getUser();
        $customer->setUser($user);

        $this->manager->persist($customer);
        $this->manager->flush();

        $view = View::create();
        $view->setData($customer)
            ->setLocation($this->generateUrl('customer.show', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
        ;

        return $view;
    }

    /**
     * @Rest\Post(
     *     path="api/customer/{id}/delete",
     *     name="customer.delete"
     * )
     *
     * @Rest\View(statusCode=200)
     *
     * @param Customer $customer
     */
    public function delete(Customer $customer)
    {
        $this->manager->remove($customer);
        $this->manager->flush();
    }
}

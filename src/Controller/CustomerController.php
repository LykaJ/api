<?php

namespace App\Controller;

use App\Entity\Customer;
use App\EventSubscriber\ExceptionListener;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\ConstraintViolationList;

class CustomerController extends AbstractController
{
    private $repository;
    private $userRepo;
    private $manager;
    private $JWTencoder;

    public function __construct(CustomerRepository $repository, UserRepository $userRepo, ObjectManager $manager, JWTEncoderInterface $JWTEncoder)
    {
        $this->repository = $repository;
        $this->userRepo = $userRepo;
        $this->manager = $manager;
        $this->JWTencoder = $JWTEncoder;
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
            $response = new JsonResponse();
            return $response->setStatusCode(Response::HTTP_NOT_FOUND);
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
     * @return Response
     */
    public function show(Customer $customer, SerializerInterface $serializer)
    {
        $data = $serializer->serialize($customer, 'json');
        $response = new Response($data);

        $date = $customer->getCreatedAt();

        $response
            ->setStatusCode(Response::HTTP_OK)
            ->setCache([
                'last_modified' => $date,
                'max_age' => 10,
                's_maxage' => 10,
                'public' => true,
            ])
            ->headers->set('Content-Type', 'application/json')
        ;

        return $response;
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
     * @param Security $security
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
        $customer->setCreatedAt(new \DateTime('now'));

        $this->manager->persist($customer);
        $this->manager->flush();

        $view = View::create();
        $view->setData([$customer, 'The customer was successfully created'])
            ->setLocation($this->generateUrl('customer.show', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
        ;

        return $view;
    }

    /**
     * @Rest\Post(
     *     path="api/customer/delete/{id}",
     *     name="customer.delete"
     * )
     *
     * @Rest\View(statusCode=200)
     * @param Customer $customer
     * @return JsonResponse
     */
    public function delete(Customer $customer)
    {
        $this->manager->remove($customer);
        $this->manager->flush();

        $response = new JsonResponse();
        $response->setData(['message' => 'The customer was successfully deleted']);
        $response->setStatusCode(Response::HTTP_OK);

        if (!$customer)
        {
            return $response->setData(['data' => 'This customer does not exist'])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $response;
    }
}

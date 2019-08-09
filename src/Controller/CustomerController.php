<?php

namespace App\Controller;

use App\Entity\Customer;
use App\EventSubscriber\ExceptionListener;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\ConstraintViolationList;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

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
     * @SWG\Response(
     *     response=200,
     *     description="Get the list of customers for the authentified user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="204",
     *     description="The user has no customers"
     * )
     *
     * @SWG\Response(
     *     response="401",
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="Bearer {YourAccessToken}"
     * )
     *
     * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
     * @SWG\Tag(name="Customers")
     *
     * @param Security $security
     * @return mixed|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function listAction(Security $security, SerializerInterface $serializer, Request $request)
    {
        $user = $security->getToken()->getUser();
        $customers = $this->repository->findByUser($user);

        $requestLimit = $request->get('limit');

        if (!$requestLimit) {
            $limit = 15;

        } else {
            $limit = $requestLimit;
            $customers = $this->repository->findByUserAndLimit($user, $limit);
        }

        $page = 1;
        $numberOfPages = (int)ceil(count($customers) / $limit);

        $collection = new CollectionRepresentation(
            $customers
        );

        $paginated = new PaginatedRepresentation(
            $collection,
            'customers',
            array(),
            $page,
            $limit,
            $numberOfPages
        );

        $data = $serializer->serialize($paginated, 'json');

        $response = new Response($data);
        $response
            ->setEtag(md5($response->getContent()))
            ->setCache([
                'etag' => $response->getEtag(),
                'public' => true
            ])
            ->isNotModified($request);

        if (!$customers) {
            $jsonResponse = new JsonResponse();
            $jsonResponse
                ->setStatusCode(Response::HTTP_NO_CONTENT);
            return $jsonResponse;
        }

        return $response;
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
     * @SWG\Response(
     *     response=200,
     *     description="Get detailled view of a customer",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="401",
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="Bearer {YourAccessToken}"
     * )
     *
     * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
     * @SWG\Tag(name="Customers")
     *
     * @param Customer $customer
     * @return Response
     */
    public function show(SerializerInterface $serializer, Request $request, Security $security)
    {
        $customer = $this->repository->find($request->attributes->get('id'));

        $jsonResponse = new JsonResponse();
        $currentUser = $security->getToken()->getUser();

        if ($customer) {
            $data = $serializer->serialize($customer, 'json');
            $response = new Response($data);

            $user = $customer->getUser();
            if ($currentUser === $user) {
                $date = $customer->getCreatedAt();

                if (isset($date)) {
                    $response
                        ->setEtag(md5($response->getContent()))
                        ->setCache([
                            'last_modified' => $date,
                            'etag' => $response->getEtag(),
                            'public' => true,
                        ])
                        ->isNotModified($request);

                    if ($response->isNotModified($request)) {
                        return $response->setStatusCode(Response::HTTP_NOT_MODIFIED);
                    }
                } else {
                    return $jsonResponse->setData(['message' => 'This customer does not exist']);
                }

            } else {
                return $jsonResponse
                    ->setData(['message' => 'You do not have access to this data.'])
                    ->setStatusCode(Response::HTTP_FORBIDDEN);
            }
            return $response;
        } else {
            return $jsonResponse->setData(['message' => 'This id is not attached to a customer. Please try another id'])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\Post(
     *     path="api/customer",
     *     name="customer.create"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("customer", converter="fos_rest.request_body")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Create a new customer",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="401",
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="Bearer {YourAccessToken}"
     * )
     *
     * @SWG\Tag(name="Customers")
     * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
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
            ->setLocation($this->generateUrl('customer.show', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL));

        return $view;
    }

    /**
     * @Rest\Delete(
     *     path="api/customer/delete/{id}",
     *     name="customer.delete"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete a customer",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="401",
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="Bearer {YourAccessToken}"
     * )
     *
     * @SWG\Tag(name="Customers")
     * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
     *
     * @Rest\View(statusCode=200)
     * @param Customer $customer
     * @return JsonResponse
     */
    public function delete(Request $request, Security $security)
    {
        $customer = $this->repository->find($request->attributes->get('id'));
        $response = new JsonResponse();

        $user = $security->getToken()->getUser();

        if ($customer) {
            if ($user === $customer->getUser()) {
                $this->manager->remove($customer);
                $this->manager->flush();

                $response->setData(['message' => 'The customer was successfully deleted'])->setStatusCode(Response::HTTP_OK);

            } else {
                $response->setData(['message' => 'You cannot delete this customer'])->setStatusCode(Response::HTTP_FORBIDDEN);
            }
        } else {
            return $response->setData(['message' => 'This customer does not exist'])->setStatusCode(Response::HTTP_BAD_REQUEST);

        }
        return $response;
    }
}

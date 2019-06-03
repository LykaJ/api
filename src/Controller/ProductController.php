<?php

namespace App\Controller;

use App\Entity\Product;
use App\EventSubscriber\ExceptionListener;
use App\Exception\ResourceValidationException;
use App\Repository\ProductRepository;
use Blackfire\Client;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class ProductController extends AbstractController
{
    private $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Rest\Get(
     *     path="/product/{id}",
     *     name="product.show",
     *     requirements={"id"="\d+"}
     * )
     *
     * @Rest\View(statusCode=200)
     *
     * @Cache(expires="tomorrow")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the detailled view of a product",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="204",
     *     description="This product does not exist"
     * )
     *
     * @SWG\Tag(name="Products")
     *
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function show(Product $product, SerializerInterface $serializer, Request $request)
    {
        $data = $serializer->serialize($product, 'json');
        $response = new Response($data);

        if (!$product)
        {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ;
        }

        $date = new \DateTime($product->getEditedAt());

        $response
            ->setEtag(md5($response->getContent()))
            ->setSharedMaxAge(3600)
            ->setCache([
                'last_modified' => $date,
                'etag' => $response->getEtag(),
                'public' => true,
            ])
            ->isNotModified($request)
        ;

        if ($response->isNotModified($request))
        {
           return $response->setStatusCode(Response::HTTP_NOT_MODIFIED);
        }

        return $response;
    }

    /**
     * @Rest\Post(
     *     path="api/product",
     *     name="product.create"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("product", converter="fos_rest.request_body")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Create a new product",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Product::class))
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
     * @SWG\Tag(name="Products")
     * @Security(name="Bearer")
     *
     * @param Product $product
     * @param ConstraintViolationList $violations
     * @param ExceptionListener $listener
     * @param \Symfony\Component\Security\Core\Security $security
     * @return View|JsonResponse
     * @throws ResourceValidationException
     */
    public function create(Product $product, ConstraintViolationList $violations, ExceptionListener $listener, \Symfony\Component\Security\Core\Security $security)
    {
        $currentUser = $security->getToken()->getUser();
        $currentRole = $currentUser->getRole();


        if ($currentRole === 'ROLE_ADMIN')
        {
            $listener->getViolations($violations);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();

            $view = View::create();
            $view->setData(['message' => 'The product was successfully created'])
                ->setLocation($this->generateUrl('product.show', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
            ;

            return $view;

        } else {
            $jsonResponse = new JsonResponse();
            return $jsonResponse->setData(['message' => 'Access denied'])->setStatusCode(Response::HTTP_FORBIDDEN);
        }


    }

    /**
     *
     * @Rest\Get(
     *     path="products",
     *     name="products"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Get the list of products",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Product::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="204",
     *     description="No product found"
     * )
     *
     * @SWG\Tag(name="Products")
     *
     * @Rest\View()
     *
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function listAction(SerializerInterface $serializer, Request $request)
    {
        $blackfire = new Client();

        $config = (new \Blackfire\Profile\Configuration())->setTitle('Products');

        try{
            $probe = $blackfire->createProbe($config);

            $products = $this->repository->findAll();
            $requestLimit = $request->get('limit');

            if (!$requestLimit)
            {
                $limit = 15;

            } else {
                $limit = $requestLimit;
                $products = $this->repository->findByLimit($limit);
            }

            $page = 1;
            $numberOfPages = (int) ceil(count($products) / $limit);

            $collection = new CollectionRepresentation(
                $products
            );

            $paginated = new PaginatedRepresentation(
                $collection,
                'products',
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
                ->isNotModified($request)
            ;

            if (!$products) {
                $response = new JsonResponse();
                return $response->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            $profile = $blackfire->endProbe($probe);

            return $response;

        } catch (\Blackfire\Exception\ExceptionInterface $e) {

            throw new \Exception("BlackFire could not profile data", 400);
        }
    }
}

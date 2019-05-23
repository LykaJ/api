<?php

namespace App\Controller;

use App\Entity\Product;
use App\EventSubscriber\ExceptionListener;
use App\Exception\ResourceValidationException;
use App\Repository\ProductRepository;
use App\Representation\Products;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

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
     * @Rest\View(statusCode=200)
     *
     * @Cache(expires="tomorrow")
     *
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
            ->setStatusCode(Response::HTTP_OK)
            ->setEtag(md5($response->getContent()))
            ->setCache([
                'last_modified' => $date,
                'etag' => $response->getEtag(),
                'public' => true,
            ])
            ->headers->set('Content-Type', 'application/json')
        ;

        if ($response->isNotModified($request))
        {
           return $response->setStatusCode(Response::HTTP_NOT_MODIFIED);
        }

        return $response;
    }

    /**
     * @Rest\Post(
     *     path="product",
     *     name="product.create"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("product", converter="fos_rest.request_body")
     *
     * @param Product $product
     * @param ConstraintViolationList $violations
     * @return View
     * @throws ResourceValidationException
     */
    public function create(Product $product, ConstraintViolationList $violations, ExceptionListener $listener)
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
    }

    /**
     * @Rest\Get(
     *     path="products",
     *     name="product.list"
     * )
     *
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     nullable=true,
     *     description="Keyword to search for"
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of product per page"
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="0",
     *     description="The pagination offset"
     * )
     * @Rest\View()
     */
    public function listAction(ParamFetcherInterface $fetcher)
    {
        $pager = $this->repository->search(
            $fetcher->get('keyword'),
            $fetcher->get('order'),
            $fetcher->get('limit'),
            $fetcher->get('offset')
        );

        return new Products($pager);
    }
}

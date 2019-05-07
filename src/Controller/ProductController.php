<?php

namespace App\Controller;

use App\Entity\Product;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends AbstractController
{
    /**
     * @Rest\Get(
     *     path="/product/{id}",
     *     name="product.show",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View()
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * @Rest\Post(
     *     path="product",
     *     name="product.create"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("product", converter="fos_rest.request_body")
     * @param SerializerInterface $serializer
     * @param Request $request
     */
    public function create(Product $product)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($product);
        $manager->flush();

        $view = View::create();
        $view->setData($product)
            ->setLocation($this->generateUrl('product.show', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
        ;

        return $view;
    }

    /**
     * @Route("/products", name="product.list", methods={"GET"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function listAction(SerializerInterface $serializer)
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        $data = $serializer->serialize($products, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}

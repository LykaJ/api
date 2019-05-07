<?php

namespace App\Controller;

use App\Entity\Product;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/{id}", name="product.show")
     */
    public function show(SerializerInterface $serializer, Product $product)
    {
        $data = $serializer->serialize($product, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/product", name="product.create", methods={"POST"})
     * @param SerializerInterface $serializer
     * @param Request $request
     */
    public function create(SerializerInterface $serializer, Request $request)
    {
        $data = $request->getContent();
        $product = $serializer->deserialize($data, Product::class, 'json');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        return new Response('', Response::HTTP_CREATED);
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

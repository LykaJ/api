<?php

namespace App\Controller;

use App\Entity\Client;
use App\EventSubscriber\ExceptionListener;
use App\Repository\ClientRepository;
use App\Representation\Clients;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class ClientController extends AbstractController
{
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Rest\Get(
     *     path="client/{id}",
     *     name="client.show",
     *     requirements={"id"="\d+"}
     * )
     *
     * @Rest\View()
     *
     * @param Client $client
     * @return Client
     */
    public function show(Client $client)
    {
        return $client;
    }

    /**
     * @Rest\Post(
     *     path="client",
     *     name="client.create"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("client", converter="fos_rest.request_body")
     *
     * @param Client $client
     * @param ConstraintViolationList $violations
     * @param ExceptionListener $listener
     * @return View
     * @throws \App\Exception\ResourceValidationException
     */
    public function create(Client $client, ConstraintViolationList $violations, ExceptionListener $listener)
    {
        $listener->getViolations($violations);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($client);
        $manager->flush();

        $view = View::create();
        $view->setData($client)
            ->setLocation($this->generateUrl('client.show', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
        ;

        return $view;
    }

    /**
     * /**
     * @Rest\Get(
     *     path="clients",
     *     name="client.list"
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
     *     default="5",
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

        return new Clients($pager);

    }
}

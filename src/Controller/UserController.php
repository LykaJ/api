<?php

namespace App\Controller;

use App\Entity\User;
use App\EventSubscriber\ExceptionListener;
use App\Pager\Pager;
use App\Repository\UserRepository;
use App\Representation\Users;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class UserController extends AbstractController
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Rest\Get(
     *     path="user/{id}",
     *     name="user.show",
     *     requirements={"id"="\d+"}
     * )
     *
     * @Rest\View()
     *
     * @param User $client
     * @return User
     */
    public function show(User $client)
    {
        return $client;
    }

    /**
     * @Rest\Post(
     *     path="user",
     *     name="user.create"
     * )
     * @Rest\View(StatusCode=201)
     * @ParamConverter("client", converter="fos_rest.request_body")
     *
     * @param User $client
     * @param ConstraintViolationList $violations
     * @param ExceptionListener $listener
     * @return View
     * @throws \App\Exception\ResourceValidationException
     */
    public function create(User $client, ConstraintViolationList $violations, ExceptionListener $listener)
    {
        $listener->getViolations($violations);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($client);
        $manager->flush();

        $view = View::create();
        $view->setData($client)
            ->setLocation($this->generateUrl('user.show', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
        ;

        return $view;
    }

    /**
     * /**
     * @Rest\Get(
     *     path="users",
     *     name="user.list"
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

        return new Users($pager);

    }
}

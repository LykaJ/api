<?php

namespace App\Controller;



use App\Entity\User;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Rest\Get(
     *     path="/user/{id}",
     *     name="user.show",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode=200)
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get the detailled view of a user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="401",
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     *
     * @SWG\Tag(name="Users")
     *
     */
    public function show(User $user)
    {
        if (!$user)
        {
            $response = new JsonResponse();
            return $response->setData(['message' => "No user attached to this id"])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $user;
    }


    /**
     * @Rest\Get(
     *     path="/users",
     *     name="users"
     * )

     * @Rest\View()
     *
     * @SWG\Response(
     *     response="200",
     *     description="Get the list of users",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response="401",
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     *
     * @SWG\Tag(name="Users")
     */
    public function listAction(Request $request, SerializerInterface $serializer)
    {
        $users = $this->repository->findAll();
        $requestLimit = $request->get('limit');

        if (!$requestLimit)
        {
            $limit = 15;

        } else {
            $limit = $requestLimit;
            $users = $this->repository->findAll();
        }

        $page = 1;
        $numberOfPages = (int) ceil(count($users) / $limit);

        $collection = new CollectionRepresentation(
            $users
        );

        $paginated = new PaginatedRepresentation(
            $collection,
            'users',
            array (),
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

        if (!$users) {
            $response = new JsonResponse();
            return $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return $response;
    }
}

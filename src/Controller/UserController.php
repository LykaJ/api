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
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Rest\Get(
     *     path="api/users/{id}",
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
     *     response="400",
     *     description="Bad request"
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
     * @SWG\Tag(name="Users")
     * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
     *
     */
    public function show(User $user, Security $security)
    {
        $currentUser = $security->getToken()->getUser();
        $currentRole = $currentUser->getRole();

        $response = new JsonResponse();

        if (!$user)
        {
            return $response->setData(['message' => "No user attached to this id"])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if ($currentRole === 'ROLE_ADMIN')
        {
            return $user;
        } else {
            return $response->setData(['message' => "Access denied"])->setStatusCode(Response::HTTP_FORBIDDEN);
        }

    }


    /**
     * @Rest\Get(
     *     path="api/users",
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
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     required=true,
     *     description="Bearer {YourAccessToken}"
     * )
     *
     * @SWG\Tag(name="Users")
     * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Security $security
     * @return JsonResponse|Response
     */
    public function listAction(Request $request, SerializerInterface $serializer, Security $security)
    {
        $users = $this->repository->findAll();
        $requestLimit = $request->get('limit');

        $currentUser = $security->getToken()->getUser();
        $currentRole = $currentUser->getRole();

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

        if ($currentRole === 'ROLE_ADMIN')
        {
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
        } else {
            $jsonResponse = new JsonResponse();

            return $jsonResponse->setData(['message' => 'Access denied'])->setStatusCode(Response::HTTP_FORBIDDEN);
        }


    }
}

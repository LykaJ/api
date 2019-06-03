<?php

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    /**
     * @Rest\Post(
     *      path="register",
     *      name="user.register"
     * )
     *
     * @Rest\View(StatusCode=201)
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return View
     *
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();

        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setRole('ROLE_USER');
        $em->persist($user);
        $em->flush();

        $view = View::create();
        $view->setData($user)
            ->setLocation($this->generateUrl('api', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
        ;

        return $view;
    }

    /**
     * @Route("/api", name="api", methods={"GET|POST|DELETE"})
     * @SWG\Response(
     *     response=200,
     *     description="Login",
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
     * @SWG\Tag(name="Authorization")
     * @Security(name="Bearer")
     *
     * @return Response
     */
    public function api()
    {
        return new Response(sprintf('Logged in as %s', $this->getUser()->getUsername()));
    }
}
<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserApiController extends AbstractController
{
    #[Route('/signUp', name: 'app_user_signUpapi')]
    public function SignUpapi(Request $request, ManagerRegistry $doctrine): Response
    {
        //enter user fields
        $nom = $request->query->get("nom");
        $prenom = $request->query->get("prenom");
        $email = $request->query->get("email");
        $motDePasse = $request->query->get("motDePasse");
        $genre = $request->query->get("genre");
        $verification_token = bin2hex(random_bytes(32));
        // controle de saisie email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new response("invalid email ");
        }
        // set the user 
        $user = new User();
        $user->setVerificationToken($verification_token);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setMotDePasse($motDePasse);
        $user->setGenre($genre);

        try {
            // persist the user in database 
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            //json response
            return new JsonResponse("Account created successfully", Response::HTTP_OK);
        } catch (\Exception $ex) {
            //if exception
            return new JsonResponse(["message" => "exception", "error" => $ex->getMessage()], 500);
        }
    }


    #[Route('/SignIn', name: 'app_user_SignInapi')]
    public function SignInapi(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine)
    {
        $email = $request->query->get("email");
        $motDePasse = $request->query->get("motDePasse");

        //to get user from db
        $entityManager = $doctrine->getManager();
        //search if user exists by email
        $user = $userRepository->findOneBy(['email' => $email]);
        // if user found 
        if ($user) {
            // check password
            if ($motDePasse == $user->getMotDePasse()) {
                // dump($user);
                // die;

                // serialize the object user  
                $serializer = new Serializer([new ObjectNormalizer()]);
                $user = $serializer->normalize($user);
                return new JsonResponse($user);
            } else {
                return new JsonResponse("Wrong password");
            }
        } else {
            return new JsonResponse("user doesn't exist or wrong email");
        }
    }
    #[Route('/get', name: 'app_user_getAllusers')]
    public function getAllusers(UserRepository $userRepository)
    {
        $users = $userRepository->findAll();
        // serialize the object   
        $serializer = new Serializer([new ObjectNormalizer()]);
        $users = $serializer->normalize($users);
        return new JsonResponse($users);
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserApiController extends AbstractController
{
    #[Route('/signUp', name: 'app_user_signUpapi')]
    public function SignUpapi(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer): Response
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

            // Send the activation email to the user
            $email = (new Email())
                ->from('Sustainability-Hub@esprit.tn')
                ->to($user->getEmail())
                ->subject('Welcome to SustainabilityHub!')
                ->html($this->renderView('user_api/ApiVerifyEmail.html.twig', [
                    'user' => $user,
                    'verificationLink' => $this->generateUrl('verify_email_api', ['token' => $verification_token], UrlGeneratorInterface::ABSOLUTE_URL),
                ]));
            $mailer->send($email);

            //json response
            return new JsonResponse("Account created successfully", Response::HTTP_OK);
        } catch (\Exception $ex) {
            //if exception
            return new JsonResponse(["message" => "exception", "error" => $ex->getMessage()], 500);
        }
    }

    //verifying the email
    #[Route('/verify_email_api/{token}', name: 'verify_email_api')]
    public function verifyEmailApi(UserRepository $userRepository, string $token)
    {
        $user = $userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Invalid verification token');
        }

        // $user->setVerificationToken('null');
        $user->setIsVerified(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('Account verified successfully , Now you can login');
    }


    #[Route('/SignIn', name: 'app_user_SignInapi')]
    public function SignInapi(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $email = $request->query->get("email");
        $motDePasse = $request->query->get("motDePasse");

        //to get user from db
        $entityManager = $doctrine->getManager();
        //search if user exists by email
        $user = $userRepository->findOneBy(['email' => $email]);
        // if user found 
        if ($user) {
            //check User verified
            if (($user->isIsVerified()) == false) {
                return new Response("Verify you email");
            } else
                // check password
                if ($motDePasse === $user->getMotDePasse()) {

                    // Set user attributes in session
                    $session->set('user', [
                        'idUser' => $user->getIdUser(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom(),
                        'email' => $user->getEmail(),
                        'motDePasse' => $user->getMotDePasse(),
                        'genre' => $user->getGenre(),
                    ]);

                    // serialize the object user  
                    $serializer = new Serializer([new ObjectNormalizer()]);
                    $user = $serializer->normalize($user);

                    return new JsonResponse($user);
                } else {
                    return new Response("Wrong password");
                }
        } else {
            return new Response("user doesn't exist or wrong email");
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

    /// update User

    #[Route('/update', name: 'app_user_update_api')]
    public function editUser(Request $request)
    {
        $idUser = $request->query->get("idUser");
        $nom = $request->query->get("nom");
        $prenom = $request->query->get("prenom");
        $email = $request->query->get("email");
        // $genre = $request->query->get("genre");
        $motDePasse = $request->query->get("motDePasse");
        $em = $this->getDoctrine()->getManager();
        // find the user in the database
        $user = $em->getRepository(User::class)->find($idUser);


        // $user->setCIN($CIN);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setMotDePasse($motDePasse);


        try {
            // persist user
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //serilize user 
            $serializer = new Serializer([new ObjectNormalizer()]);
            $user = $serializer->normalize($user);

            return new JsonResponse($user);
        } catch (\Exception $ex) {
            return new Response("failed" . $ex->getMessage());
        }
    }
}

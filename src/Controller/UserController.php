<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/ ', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('baseFront.html.twig', []);
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/users', name: 'event_list')]
    public function ListEvents(UserRepository $repository)
    {
        $users = $repository(User::class)->findAll();
        return $this->render('baseFront.html.twig', ['users' => $users]);
        // return $this->json(["users" => $users]);
    }

    //add user 
    // #[Route('/create_user', name: 'create_user')]
    // public function createUser(Request $request, ManagerRegistry $doctrine): Response
    // {
    //     $user = new User();
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $doctrine->getManager();
    //         $entityManager->persist($user);
    //         $entityManager->flush();
    //         return $this->json(['message' => $user]);
    //         // return $this->redirectToRoute('event_list');
    //     }
    //     // return $this->render('event/add.html.twig', [
    //     //     'form' => $form->createView(),
    //     // ]);
    //     return $this->json(['erro' => $form->getErrors()]);
    // }

    #[Route('/create_user', name: 'create_user')]
    public function createUser(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine)
    { {
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                // return $this->json(['message' => ' creating user', $user]);
                return $this->redirectToRoute('app_home');
            }
            return $this->renderForm('user/userRegister.html.twig', [

                'userForm' => $form,
            ]);
        }
    }



    // update user 
    #[Route('/update_user/{id}', name: 'update_user', methods: "PUT")]
    public function updateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $user = $userRepository->findUserById($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setMotDePasse($data['mot_de_passe']);
        $user->setGenre($data['genre']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User updated successfully', $user]);
    }
    // ALTERNATIVE 
    // public function updateUser(User $user, Request $request, EntityManagerInterface $entityManager)
    // {
    //     $form = $this->createForm(UserType::class, $user);
    //     $data = json_decode($request->getContent(), true);
    //     $form->submit($data);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->persist($user);
    //         $entityManager->flush();

    //         return $this->json(['message' => 'User updated successfully']);
    //     }

    //     return $this->json(['error' => $form->getErrors()], 400);
    // }

    // show a user 
    #[Route('/user/{id}', name: 'show_user', methods: "GET")]
    public function showUser(User $user)
    {
        return $this->json($user);
    }





    //Delete user 
    #[Route('/delete_user/{id}', name: 'delete_user', methods: "DELETE")]
    public function deleteUser(User $user, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }


    #[Route('/userCreate', name: 'Create_user')]
    public function create(Request $request): Response
    {



        $entityManager = $this->getDoctrine()->getManager();

        $user = new User();


        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);


            $entityManager->flush();
            return $this->redirectToRoute('app_user');
        } else {
            return $this->render(
                "user/userRegister.html.twig",
                [
                    'userForm' => $form->createView()
                ]
            );
        }
    }
}
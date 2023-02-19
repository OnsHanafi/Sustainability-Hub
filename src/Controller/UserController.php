<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\UpdateUserType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    #[Route('/homeUser ', name: 'app_home_user')]
    public function homeUser(): Response
    {
        return $this->render('baseFront.html.twig', []);
    }

    #[Route('/homeAdmin ', name: 'app_home_admin')]
    public function homeAdmin(): Response
    {
        return $this->render('baseBack.html.twig', []);
    }


    // #[Route('/user', name: 'app_user')]
    // public function index(): Response
    // {
    //     return $this->render('user/index.html.twig', [
    //         'controller_name' => 'UserController',
    //     ]);
    // }

    #[Route('/users', name: 'app_users')]
    public function ListEvents(UserRepository $userRepository, SessionInterface $session)
    {
        $users = $userRepository->findAll();
        $userId = $session->get('user')['idUser'];
        $loggedInUser = $userRepository->find($userId);
        return $this->render('user/index.html.twig', ['users' => $users, 'loggedInUser' => $loggedInUser]);
    }

    //add user 
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
                return $this->redirectToRoute('app_login');
            }
            return $this->renderForm('user/userRegister.html.twig', [

                'userForm' => $form,
            ]);
        }
    }

    //login
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, UserRepository $userRepository,  SessionInterface $session): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if (!$existingUser) {
                // throw new BadCredentialsException('Invalid email or password');
            }
            // if password doesn't match : throw new BadCredentialsException('Invalid email or password');

            if ($user->getMotDePasse() == $existingUser->getMotDePasse()) {


                // Set user attributes in session
                $session->set('user', [
                    'idUser' => $existingUser->getIdUser(),
                    'nom' => $existingUser->getNom(),
                    'prenom' => $existingUser->getPrenom(),
                    'email' => $existingUser->getEmail(),
                    'motDePasse' => $existingUser->getMotDePasse(),
                    'genre' => $existingUser->getGenre(),
                ]);
                return $this->redirectToRoute('show_user', ['idUser' => $existingUser->getIdUser()]);
            }
        }

        return $this->render('user/login.html.twig', [
            'loginForm' => $form->createView(),
        ]);
    }

    //Profile
    #[Route('/user/{idUser}', name: 'show_user')]
    public function showUser(SessionInterface $session, UserRepository $userRepository)
    {
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        if ($user->getGenre() === 'admin') {
            return $this->render('user/profile/adminProfile.html.twig', [
                'user' => $user,
            ]);
        } else {
            return $this->render('user/profile/userProfile.html.twig', [
                'user' => $user,
            ]);
        }
    }




    // update user 
    #[Route('/update_user/{id}', name: 'update_user')]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, UserRepository $userRepository)
    {
        // get the user's data from the session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->findUserById($userId);
        // create a form to update the user's information
        $form = $this->createForm(UpdateUserType::class);

        // handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // retrieve the updated user data from the form
            $updatedUser = $form->getData();

            // update the user entity with the new data
            $user->setNom($updatedUser->getNom());
            $user->setPrenom($updatedUser->getPrenom());
            $user->setEmail($updatedUser->getEmail());
            $user->setMotDePasse($updatedUser->getMotDePasse());
            // update the user in the database
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully.');

            return $this->redirectToRoute('show_user', ['idUser' => $user->getIdUser()]);
        }

        if ($user->getGenre() === 'admin') {
            return $this->renderForm('user/update/adminUpdate.html.twig', [
                'form' => $form,
                'user' => $user,
            ]);
        } else {
            return $this->renderForm('user/update/userUpdate.html.twig', [
                'form' => $form,
                'user' => $user,
            ]);
        }
    }


    // public function updateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager)
    // {
    // $user = $userRepository->findUserById($id);

    // if (!$user) {
    //     return $this->json(['error' => 'User not found'], 404);
    // }

    // $data = json_decode($request->getContent(), true);
    // $user->setNom($data['nom']);
    // $user->setPrenom($data['prenom']);
    // $user->setEmail($data['email']);
    // $user->setMotDePasse($data['motDePasse']);
    // $user->setGenre($data['genre']);

    // $entityManager->persist($user);
    // $entityManager->flush();

    // return $this->json(['message' => 'User updated successfully', $user]);
    // }
    // ALTERNATIVE 


    //Delete user 
    #[Route('/delete_user/{id}', name: 'delete_user')]
    public function deleteUser(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, $id)
    {
        // find the user to delete
        $user = $userRepository->find($id);

        //delete from DB
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('create_user');
    }

    //Delete user AfminSide 
    #[Route('/delete_user/{id}', name: 'delete_user_Admin')]
    public function deleteUserAdmin(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, $id)
    {
        // find the user to delete
        $user = $userRepository->find($id);

        //delete from DB
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_users');
    }


    // #[Route('/userCreate', name: 'Create_user')]
    // public function create(Request $request): Response
    // {



    //     $entityManager = $this->getDoctrine()->getManager();

    //     $user = new User();


    //     $form = $this->createForm(UserType::class, $user);

    //     $form->handleRequest($request);
    //     if ($form->isSubmitted() && $form->isValid()) {

    //         $entityManager->persist($user);


    //         $entityManager->flush();
    //         return $this->redirectToRoute('app_user');
    //     } else {
    //         return $this->render(
    //             "user/userRegister.html.twig",
    //             [
    //                 'userForm' => $form->createView()
    //             ]
    //         );
    //     }
    // }
}
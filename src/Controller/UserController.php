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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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



    //add user 
    #[Route('/create_user', name: 'create_user')]
    public function createUser(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine)
    { {
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser) {
                    $form->get('email')->addError(new FormError('Email Already exists , Change it !!'));
                } else {
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_login');
                }
            }
            return $this->renderForm('user/userRegister.html.twig', [

                'userForm' => $form,
            ]);
        }
    }

    //login
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, UserRepository $userRepository, SessionInterface $session): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get user from form to check for errors
            $user = $form->getData();
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            // User doesn't exist 
            if (!$existingUser) {
                $form->get('email')->addError(new FormError('Email doesn`t exist'));
                // wrong password
            } elseif ($user->getMotDePasse() != $existingUser->getMotDePasse()) {
                $form->get('motDePasse')->addError(new FormError('Incorrect password'));
                // everything is wright !!
            } else if ($user->getMotDePasse() == $existingUser->getMotDePasse()) {
                // Set user attributes in session
                $session->set('user', [
                    'idUser' => $existingUser->getIdUser(),
                    'nom' => $existingUser->getNom(),
                    'prenom' => $existingUser->getPrenom(),
                    'email' => $existingUser->getEmail(),
                    'motDePasse' => $existingUser->getMotDePasse(),
                    'genre' => $existingUser->getGenre(),
                ]);
                return $this->redirectToRoute('show_user', ['id' => $existingUser->getIdUser()]);
            }
        }

        return $this->render('user/login.html.twig', [
            'loginForm' => $form->createView(),
        ]);
    }



    //Profile
    #[Route('/user/{id}', name: 'show_user')]
    public function showUser(SessionInterface $session, UserRepository $userRepository)
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);
        // User 404
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        // User = admin
        if ($user->getGenre() === 'admin') {
            return $this->render('user/profile/adminProfile.html.twig', [
                'user' => $user,
            ]);
        } else {
            // User != admin
            return $this->render('user/profile/userProfile.html.twig', [
                'user' => $user,
            ]);
        }
    }




    // update user + admin
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
            $existingUser = $userRepository->findOneBy(['email' => $updatedUser->getEmail()]);
            // error if the email already exists in the DB
            if ($existingUser &&  ($existingUser->getIdUser() != $user->getIdUser())) {
                $form->get('email')->addError(new FormError('Email Already exists'));
            } else {
                // update the user entity with the new data
                $user->setNom($updatedUser->getNom());
                $user->setPrenom($updatedUser->getPrenom());
                $user->setEmail($updatedUser->getEmail());
                $user->setMotDePasse($updatedUser->getMotDePasse());
                // update the user in the database
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'User updated successfully.');

                return $this->redirectToRoute('show_user', ['id' => $user->getIdUser()]);
            }
        }
        // rendering twigs depending on user's genre
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

    ////////////// Admin gestion users //////////////////////////////

    // get users list 
    #[Route('/admin/users', name: 'app_users')]
    public function ListEvents(UserRepository $userRepository, SessionInterface $session)
    {   // getting users
        $users = $userRepository->findAll();
        // the admins id
        $userId = $session->get('user')['idUser'];
        $loggedInUser = $userRepository->find($userId);
        // dump($loggedInUser);
        // render the users list
        return $this->render('user/admin/index.html.twig', ['users' => $users, 'user' => $loggedInUser]);
    }

    //Delete user AfminSide 
    #[Route('/admin/delete_user/{id}', name: 'delete_user_Admin')]
    public function deleteUserAdmin(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, $id)
    {
        // find the user to delete
        $user = $userRepository->find($id);

        //delete from DB
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_users');
    }



    #[Route('/admin/users/edit/{id}', name: 'admin_edit_user')]
    public function editUserAdmin(Request $request, User $user, UserRepository $userRepository)
    {
        // Get the admin
        $session = $request->getSession();

        // Get the admin user from the session
        $admin = $session->get('user')['idUser'];

        // Check if the admin user exists in the session
        if (!$admin) {
            throw new \Exception('Admin user not found in session');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // retrieve the updated user data from the form
            $user = $form->getData();
            // search if email exists or not
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            // error if the email already exists in the DB
            if ($existingUser &&  ($existingUser->getIdUser() != $user->getIdUser())) {
                $form->get('email')->addError(new FormError('Email Already exists'));
            } else {
                // Update  the user

                // $user->setNom($request->request->get('user')['nom']);
                // $user->setPrenom($request->request->get('user')['prenom']);
                // $user->setEmail($request->request->get('user')['email']);
                // $user->setMotDePasse($request->request->get('user')['motDePasse']);
                // $user->setGenre($request->request->get('user')['genre']);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();

                return $this->redirectToRoute('app_users', ['id' => $admin]);
            }
        }

        return $this->render('user/admin/adminUpdateUser.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}

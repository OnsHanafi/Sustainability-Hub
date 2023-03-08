<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\LoginType;
use App\Form\ResetPasswordType;
use App\Form\UpdateUserType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;



class UserController extends AbstractController
{
    #[Route('/homeUser ', name: 'app_home_user')]
    public function homeUser()
    {
        return $this->render('baseFront.html.twig', []);
    }

    #[Route('/homeAdmin ', name: 'app_home_admin')]
    public function homeAdmin()
    {
        return $this->render('baseBack.html.twig', []);
    }


    //add user 
    #[Route('/create_user', name: 'create_user')]
    public function createUser(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, MailerInterface $mailer)
    { {
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
            $logo = 'public/logoSH.png';

            //show password 
            $showPassword = false;
            if ($request->request->has('showPassword')) {
                $showPassword = true;
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser) {
                    $form->get('email')->addError(new FormError('Email Already exists , Change it !!'));
                } else {
                    // Generate verification token and save to user entity
                    $token = bin2hex(random_bytes(32));
                    $user->setVerificationToken($token);

                    //persist user 
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();


                    // Send verification email to user
                    $email = (new Email())
                        ->from('Sustainability-Hub@esprit.tn')
                        ->to($user->getEmail())
                        ->subject('Welcome to SustainabilityHub!')
                        ->html($this->renderView('verificationEmail.html.twig', [
                            'logo' => $logo,
                            'user' => $user,
                            'verificationLink' => $this->generateUrl('verify_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
                        ]));
                    $mailer->send($email);

                    return $this->redirectToRoute('app_login');
                }
            }



            return $this->renderForm('user/userRegister.html.twig', [

                'userForm' => $form,
                'showPassword' => $showPassword,
            ]);
        }
    }

    #[Route('/verify_email/{token}', name: 'verify_email')]
    public function verifyEmail(UserRepository $userRepository, string $token)
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

        return $this->redirectToRoute('app_login');
    }




    //login
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, UserRepository $userRepository, SessionInterface $session): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        //show password 
        $showPassword = false;
        if ($request->request->has('showPassword')) {
            $showPassword = true;
        }

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
            } elseif (!($existingUser->isIsVerified())) {
                $form->get('email')->addError(new FormError('Account not verified. Please check your email for verification instructions.'));
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
            'showPassword' => $showPassword,
        ]);
    }

    // forgot password 
    #[Route('/forgotPassword', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formEmail = $form->getData();
            $user = $userRepository->findOneBy(['email' => $formEmail->getEmail()]);


            if ($user) {
                //change the Token  for reset password
                $token = uniqid();
                $user->setVerificationToken($token);
                // persist in db
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                // email
                $email = (new TemplatedEmail())
                    ->from('Sustainability-Hub@esprit.tn')
                    ->to($user->getEmail())
                    ->subject('Reset your password')
                    ->htmlTemplate('user/reset_password_email.html.twig')
                    ->context([
                        // 'token' => $token,
                        'user' => $user,
                        'resetLink' => $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
                    ]);

                $mailer->send($email);

                $this->addFlash('success', 'An email has been sent to you with instructions on how to reset your password.');
                return $this->redirectToRoute('app_login');
            } else {
                $form->get('email')->addError(new FormError('This email doesn`t exist'));
            }
        }

        return $this->render('user/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //Reset password 
    #[Route('/resetPassword/{token}', name: 'app_reset_password')]
    public function resetPassword(Request $request, UserRepository $userRepository, string $token, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //get the password from the form 
            $formPassword = $form->getData();
            $password = $formPassword->getMotDePasse();
            // set the new password 
            // $user->setVerificationToken(null);
            $user->setMotDePasse($password);
            // persist the user in the db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your password has been reset. You can now log in with your new password.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/reset_password.html.twig', [
            'form' => $form->createView(),
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

    // Logout 
    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        // remove user from session
        $session->remove('user');
        // redirect to login 
        return $this->redirectToRoute('app_login');
    }



    ////////////// Admin gestion users //////////////////////////////

    // get users list 
    #[Route('/admin/users', name: 'app_users')]
    public function ListUsers(UserRepository $userRepository, SessionInterface $session)
    {
        // getting users
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

    // serch users 

    #[Route("/admin/search-users", name: "search_users")]
    public function searchUser(Request $request, UserRepository $userRepository)
    {
        $searchTerm = $request->request->get('searchTerm');
        if ($searchTerm === null) {
            return new Response("searchTerm parameter missing");
        }

        $users = $userRepository->findBySearchTerm($searchTerm);
        $foundUsers = false;
        if ($users == null) {
            return new Response("no users found");
        }
        $response = [];

        foreach ($users as $Otheruser) {
            $foundUsers = true;
            $response[] = [
                'idUser' => $Otheruser->getIdUser(),
                'nom' => $Otheruser->getNom(),
                'prenom' => $Otheruser->getPrenom(),
                'email' => $Otheruser->getEmail(),
                'genre' => $Otheruser->getGenre(),
            ];
        }
        if (!$foundUsers) {
            return new JsonResponse("no users found");
        } else {
            return new JsonResponse($response);
        }
    }
}

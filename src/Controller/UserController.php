<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/users', name: 'event_list')]
    public function ListEvents(UserRepository $repository): Response
    {
        $users = $repository->findAll();
        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    //add user 
    #[Route('/create_user', name: 'app_user', methods: "POST")]
    public function createUser(Request $request, EntityManager $entityManager)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json(['message' => 'User created successfully']);
        }

        return $this->json(['error' => $form->getErrors()], 400);
        // return $this->render('event/add.html.twig',[
        //     'form'=> $form->createView(),
        // ]);
    }

    // update user 
    #[Route('/update_user/{id}', name: 'update_user', methods: "PUT")]
    public function updateUser(User $user, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json(['message' => 'User updated successfully']);
        }

        return $this->json(['error' => $form->getErrors()], 400);
    }

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
}

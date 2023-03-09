<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{

    #[Route('/back', name: 'app_back_i', methods: ['GET'])]
    public function indexx(SessionInterface $session, UserRepository $userRepository,PostRepository $postRepository): Response
    {
        try {
           // get logged in user from session
         $userId = $session->get('user')['idUser'];
         $user = $userRepository->find($userId);
        } catch (\Throwable $th) {
            return $this->redirectToRoute('create_user');
        }
        

        return $this->render('post/index_back.html.twig', [
            'posts' => $postRepository->findAll(),
            'user' => $user
        ]);
    }

    #[Route('/showPost/{id}', name: 'app_show', methods: ['GET'])]
    public function show(SessionInterface $session, UserRepository $userRepository,Post $post,CommentRepository $commentRepository,Request $request): Response
    {
         // get logged in user from session
         $userId = $session->get('user')['idUser'];
         $user = $userRepository->find($userId);

        return $this->render('post/show_back.html.twig', [
            'post' => $post,
            'user' => $user
        ]);
    }

}

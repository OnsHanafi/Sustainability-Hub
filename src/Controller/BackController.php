<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{

    #[Route('/back', name: 'app_back_i', methods: ['GET'])]
    public function indexx(PostRepository $postRepository): Response
    {
        return $this->render('post/index_back.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }
    #[Route('/{id}', name: 'app_show', methods: ['GET'])]
    public function show(Post $post,CommentRepository $commentRepository,Request $request): Response
    {
        return $this->render('post/show_back.html.twig', [
            'post' => $post,
        ]);
    }

}

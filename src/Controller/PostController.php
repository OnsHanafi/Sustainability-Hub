<?php

namespace App\Controller;

use     App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/p', name: 'app_post_index', methods: ['GET'])]
    public function index(SessionInterface $session, UserRepository $userRepository,PostRepository $postRepository): Response
    {   
        try {
             // get logged in user from session
         $userId = $session->get('user')['idUser'];
         $user = $userRepository->find($userId);

        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
            'user' => $user
        ]);
        } catch (\Throwable $th) {
            return $this->redirectToRoute('create_user');
        }

        
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(SessionInterface $session, UserRepository $userRepository,Request $request, PostRepository $postRepository, MailerInterface $mailer): Response
    {
         // get logged in user from session
         $userId = $session->get('user')['idUser'];
         $user = $userRepository->find($userId);

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rr = $this->filterwords($post->getTitle(), $mailer);

            $post->setTitle($rr);
            $rr = $this->filterwords($post->getDetails(), $mailer);

            $post->setDetails($rr);
            $post->setDateP(new \DateTime('now'));
            $post->setRate(0);

            $postRepository->save($post, true);

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(SessionInterface $session, UserRepository $userRepository,Post $post, CommentRepository $commentRepository, Request $request): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'user' =>$user
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(SessionInterface $session, UserRepository $userRepository,Request $request, Post $post, PostRepository $postRepository, MailerInterface $mailer): Response
    {
         // get logged in user from session
         $userId = $session->get('user')['idUser'];
         $user = $userRepository->find($userId);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rr = $this->filterwords($post->getTitle(), $mailer);

            $post->setTitle($rr);
            $rr = $this->filterwords($post->getDetails(), $mailer);

            $post->setDetails($rr);
            $post->setRate($post->getRate());
            $postRepository->save($post, true);

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'user' =>$user
            
        ]);
    }
    // delete front
    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, PostRepository $postRepository, CommentRepository $commentRepository): Response
    {

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $comments = $post->getComments();
            foreach ($comments as $comment) {
                $commentRepository->remove($comment, true);
            }
            $postRepository->remove($post, true);
        }


        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    // delete back
    #[Route('/{id}/', name: 'app_post_delet', methods: ['POST'])]
    public function deletee( Request $request, Post $post, PostRepository $postRepository, CommentRepository $commentRepository): Response
    {
       

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $comments = $post->getComments();
            foreach ($comments as $comment) {
                $commentRepository->remove($comment, true);
            }
            $postRepository->remove($post, true);
        }


        return $this->redirectToRoute('app_back_i', [], Response::HTTP_SEE_OTHER);
    }

    public function filterwords($text, MailerInterface $mailer)
    {
        $filterWords = array('fokaleya', 'bhim', 'msatek', 'fuck', 'slut', 'fucku');
        $filterCount = count($filterWords);
        $str = "";
        $data = preg_split('/\s+/', $text);
        foreach ($data as $s) {
            $g = false;
            foreach ($filterWords as $lib) {
                if ($s == $lib) {
                    $t = "";
                    for ($i = 0; $i < strlen($s); $i++) $t .= "*";
                    $str .= $t . " ";
                    $g = true;
                    $email = (new Email())
                        ->from('Sustainability-Hub@esprit.tn')
                        ->to('Example@exp.com')
                        //->cc('cc@example.com')
                        //->bcc('bcc@example.com')
                        //->replyTo('fabien@example.com')
                        //->priority(Email::PRIORITY_HIGH)
                        ->subject('Violationd de post')
                        ->text('Violationd de post')
                        ->html("<p>votre post contient un contenu indesirable</p>");

                    $mailer->send($email);
                    break;
                }
            }
            if (!$g)
                $str .= $s . " ";
        }
        return $str;
    }
}

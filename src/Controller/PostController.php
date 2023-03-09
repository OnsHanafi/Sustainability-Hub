<?php

namespace App\Controller;

use     App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/p', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostRepository $postRepository,MailerInterface $mailer): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rr = $this->filterwords($post->getTitle(),$mailer);

            $post->setTitle($rr);
            $rr = $this->filterwords($post->getDetails(),$mailer);

            $post->setDetails($rr);
            $post->setDateP(new \DateTime('now'));
            $post->setRate(0);

            $postRepository->save($post, true);

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post,CommentRepository $commentRepository,Request $request): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, PostRepository $postRepository,MailerInterface $mailer): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rr = $this->filterwords($post->getTitle(),$mailer);

            $post->setTitle($rr);
            $rr = $this->filterwords($post->getDetails(),$mailer);

            $post->setDetails($rr);
            $post->setRate($post->getRate());
            $postRepository->save($post, true);

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, PostRepository $postRepository , CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $comments=$post->getComments();
            foreach($comments as $comment){
                $commentRepository->remove($comment, true);
            }            $postRepository->remove($post, true);


        }


        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/', name: 'app_post_delet', methods: ['POST'])]
    public function deletee(Request $request, Post $post, PostRepository $postRepository , CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $comments=$post->getComments();
            foreach($comments as $comment){
                $commentRepository->remove($comment, true);
            }
            $postRepository->remove($post, true);


        }


        return $this->redirectToRoute('app_back_i', [], Response::HTTP_SEE_OTHER);    }

    public function filterwords($text,MailerInterface $mailer)
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
                        ->from('Krichene.Eya@esprit.tn')
                        ->to('Example@exp.com')
                        //->cc('cc@example.com')
                        //->bcc('bcc@example.com')
                        //->replyTo('fabien@example.com')
                        //->priority(Email::PRIORITY_HIGH)
                        ->subject('Demande de charge')
                        ->text('Demande de charge')
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

<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
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

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }
    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(SessionInterface $session, UserRepository $userRepository, $id, Request $request, Comment $comment, CommentRepository $commentRepository, MailerInterface $mailer): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rr = $this->filterwordss($comment->getDescription(), $mailer);

            $comment->setDescription($rr);
            $commentRepository->save($comment, true);

            return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
        }

        return $this->renderForm('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/{id}/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(SessionInterface $session, UserRepository $userRepository, $id, Request $request, PostRepository $rep, CommentRepository $commentRepository, MailerInterface $mailer): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $comment = new Comment();
        $comment->setPost($rep->find($id));
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rr = $this->filterwordss($comment->getDescription(), $mailer);

            $comment->setDescription($rr);
            $comment->setDateC(new \DateTime('now'));

            $commentRepository->save($comment, true);

            return $this->redirectToRoute('app_post_show', ['id' => $id]);
        }

        return $this->renderForm('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }


    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {


            $id = $comment->getPost()->getId();
            $commentRepository->remove($comment, true);
            return $this->redirectToRoute('app_post_show', ['id' => $id]);;
        }
        if ($this->isCsrfTokenValid('deletee' . $comment->getId(), $request->request->get('_tokenn'))) {


            $id = $comment->getPost()->getId();
            $commentRepository->remove($comment, true);
            return $this->redirectToRoute('app_show', ['id' => $id]);;
        }
        $id = $comment->getPost()->getId();

        return $this->redirectToRoute('app_post_show', ['id' => $id]);;
    }
    public function filterwordss($text, MailerInterface $mailer)
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
                        ->subject('Violation de commentaire')
                        ->text('Violation de commentaire')
                        ->html("<p>votre commentaire contient un contenu indesirable</p>");

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

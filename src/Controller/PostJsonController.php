<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PostJsonController extends AbstractController
{
    #[Route('/trip', name: 'app_trip')]
    public function trii(PostRepository $repo, NormalizerInterface $normalizer)
    {
        $posts = $repo->getPostDesc();
        $postserialize = $normalizer->normalize($posts, 'json', ['groups' => "posts"]);
        return new Response(json_encode($postserialize));
    }
    #[Route('/Allposts', name: 'app_post_index_jsonnn', methods: ['GET'])]
    public function indexjsonnnn(PostRepository $postRepository, NormalizerInterface $serializer)
    {
        //get all posts
        $posts = $postRepository->findAll();
        //serializing data to Json format
        $postserialize = $serializer->normalize($posts, 'json', ['groups' => "posts"]);
        $query = json_encode($postserialize);
        return new Response($query);
    }
    #[Route('/TriPost', name: 'app_tri_post_json', methods: ['GET'])]
    public function DatePostDesc(PostRepository $repo, NormalizerInterface $normalizer)
    {
        $posts = $repo->getPostDesc();
        $postserialize = $normalizer->normalize($posts, 'json', ['groups' => "posts"]);
        return new Response(json_encode($postserialize));
    }
    #[Route('newComMail', name: 'app_postMail')]
    public function addnew(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, MailerInterface $mailer): Response
    {

        $post = new Post();
        $post->setTitle($request->get('title'));
        $post->setDetails($request->get('det'));
        $post->setDateP(new \DateTime('now'));
        $post->setImage('uploads/posts/default.jpg');
        $em->persist($post);
        $em->flush();

        //envois mail
        $email = (new Email())
            ->from('ahmed.benabid@gmail.com')
            ->to('Example@expl.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Post publié')
            ->text('Check your post')
            ->html("<p>votre post est publiée avec succès</p>");

        $mailer->send($email);
        $JsonContent = $normalizer->normalize($post, 'json', ['groups' => "posts"]);
        return new Response(json_encode($JsonContent));
    }
    #[Route('/test', name: 'app_test')]
    public function indexjsonn(PostRepository $postRepository, NormalizerInterface $serializer)
    {
        //get all posts
        $posts = $postRepository->findAll();
        //serializing data to Json format
        $postserialize = $serializer->normalize($posts, 'json', ['groups' => "posts"]);
        return new Response(json_encode($postserialize));
    }

    #[Route('/json/{id}', name: 'app_post_show_json', methods: ['GET'])]
    public function showjson($id, PostRepository $postRepository, NormalizerInterface $nm)
    {

        $post = $postRepository->find($id);
        $postnm = $nm->normalize($post, 'json', ['groups' => "posts"]);

        return new Response(json_encode($postnm));
    }
    #[Route('/testdeletee', name: 'app_testdeletee')]
    public function deleteJ(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, CommentRepository $commentRepository, PostRepository $postRepository): Response
    {
        $post = $em->getRepository(Post::class)->find($request->get('id'));
        $comments = $post->getComments();
        foreach ($comments as $comment) {
            $em->remove($comment);
        }
        $em->remove($post);
        $em->flush();
        $matNormalize = $normalizer->normalize($post, 'json', ['groups' => "posts"]);

        return new Response("deleted successefully" . json_encode($matNormalize));
    }


    #[Route('/edit/json', name: 'app_post_edit_Json', methods: ['GET', 'POST'])]
    public function Jedit(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): Response
    {

        $post = $em->getRepository(Post::class)->find($request->get('id'));
        $post->setId($post->getId());
        $post->setTitle($request->get('title'));
        $post->setDetails($request->get('det'));
        $post->setDatePost($post->getDatePost());
        $post->getImage($post->getImage());
        $em->persist($post);
        $em->flush();

        $JsonContent = $normalizer->normalize($post, 'json', ['groups' => "posts"]);
        return new Response("Post updated successfully" . json_encode($JsonContent));
    }

    #[Route('/testdelete', name: 'app_post_delete_json', methods: ['GET'])]
    public function deletejJ(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): Response
    {
        $post = $em->getRepository(Post::class)->find($request->get('id'));
        $em->remove($post);
        $em->flush();
        $matNormalize = $normalizer->normalize($post, 'json', ['groups' => "posts"]);

        return new Response("deleted successefully" . json_encode($matNormalize));
    }
    //End json


}

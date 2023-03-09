<?php

namespace App\Controller;

use  App\Form\CategoryType;
use App\Entity\Category;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrin\ORM\EntityManagerInterface;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CategoryController extends AbstractController
{

    #[Route('/categoryCreate', name: 'Create_category')]
    public function create(SessionInterface $session, UserRepository $userRepository, Request $request): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);


        $entityManager = $this->getDoctrine()->getManager();

        $category = new Category();


        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($category);


            $entityManager->flush();
            return $this->redirectToRoute('show_category');
        } else {
            return $this->render(
                "Category/add.html.twig",
                [
                    'form' => $form->createView(),
                    'user' => $user
                ]
            );
        }
    }

    #[Route('/categoryShow', name: 'show_category')]


    public function showAll(SessionInterface $session, UserRepository $userRepository): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();



        return $this->render('Category/index.html.twig', ['category' => $category, 'user' => $user]);
    }
    #[Route('/categoryDelete/{id}', name: 'delete_category')]

    public function Supprimer($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $categ = $entityManager->getRepository(Category::class)->find($id);

        if (!$categ) {
            throw $this->createNotFoundException(
                'No category found for id ' . $id
            );
        }

        $entityManager->remove($categ);
        $entityManager->flush();
        return $this->redirectToRoute('show_category');
    }


    #[Route('/categoryUpdate/{id}', name: 'update_category')]
    public function update(SessionInterface $session, UserRepository $userRepository,Request $request, $id): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);


        $entityManager = $this->getDoctrine()->getManager();


        $categ = $entityManager->getRepository(Category::class)->find($id);

        $form = $this->createForm(CategoryType::class, $categ);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($categ);


            $entityManager->flush();
            return $this->redirectToRoute('show_category');
        } else {
            return $this->render(
                "Category/update.html.twig",
                [
                    'form' => $form->createView(),
                    'user' => $user
                ]
            );
        }
    }


    #[Route('/displayMobile', name: 'displayMobile')]
    public function displayServiceMobile(NormalizerInterface $normalizer): Response
    {
        $service = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $jsonContent = $normalizer->normalize($service, 'json', ['groups' => 'post:read']);
        dump($jsonContent);
        return new Response(json_encode($jsonContent));
    }

    #[Route('/addCategMobile/{type}/{description}', name: 'addCategMobile')]
    public function addCategoryMobile(Request $request, NormalizerInterface $normalizer, $type, $description): Response
    {
        $category = new Category();
        $entityManager = $this->getDoctrine()->getManager();
        // $type=$request->query->get('Type');
        $category->setType($type);

        //$description = $request->query->get('Description');
        $category->setDescription($description);
        $entityManager->persist($category);
        $entityManager->flush();
        $jsonContent = $normalizer->normalize($category, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /****************************** */



    #[Route("/deleteCategoryMobile", name: "deleteCategorie")]


    public function deleteCategoryMobile(Request $request, SerializerInterface $serializer): Response
    {
        $id = $request->query->get("id");
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);
        if ($category != null) {
            $entityManager->remove($category);
            $entityManager->flush();
            $formatted = $serializer->normalize($category, 'json', ['groups' => 'post:read']);

            return new Response(json_encode($formatted));
        }

        return new Response(" category n'existe pas");
    }



    #[Route("/UpdateCategoryMobile/{type}", name: "UpdateCategoryMobile")]

    public function updateMaisonMobile(Request $request, SerializerInterface $normalizer, $type): Response
    {
        $em = $this->getDoctrine()->getManager();
        $category = $this->getDoctrine()->getManager()
            ->getRepository(Category::class)
            ->find($request->get("id"));

        $category->setType($type);

        $category->setDescription($request->get("description"));



        $em->persist($category);
        $em->flush();
        $formatted = $normalizer->normalize($category, 'json', ['groups' => 'post:read']);
        return new Response("categorie a été modifier " . json_encode($formatted));
    }



    /*********************ApiSort******************************************* */

    #[Route("/apiSortType", name: "apiSortType")]

    public function sortjsonType(NormalizerInterface $Normalizer): Response
    {
        $categ = $this->getDoctrine()->getRepository(Category::class)->SortByType();
        $jsonContent = $Normalizer->normalize($categ, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /****************************************************************** */
    #[Route("/apiFindType/{type}", name: "apiFindType")]

    public function FindjsonType(NormalizerInterface $Normalizer, $type): Response
    {
        $categ = $this->getDoctrine()->getRepository(Category::class)->findType($type);
        $jsonContent = $Normalizer->normalize($categ, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));
    }
}

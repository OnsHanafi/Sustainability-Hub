<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Twilio\Rest\Client;

use App\Entity\PdfGeneratorService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Normalizer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ReclamationController extends AbstractController
{

    #[Route('/reclamation', name: 'app_reclamation')]
    public function index()
    {
        return $this->redirectToRoute('app_reclamation1');
    }


    #[Route('/readFront', name: 'read_front')]
    public function readFront(SessionInterface $session, UserRepository $userRepository,ReclamationRepository $ReclamationRepository): Response
    {
        try {
             // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);
        $reclamation = $ReclamationRepository->findAll();
        } catch (\Throwable $th) {
            return $this->redirectToRoute('create_user');
        }

        return $this->render('reclamation/index.html.twig', ['rec' => $reclamation, 'user'=>$user]);
    }


    #[Route('/readBack', name: 'liste')]
    public function read(SessionInterface $session, UserRepository $userRepository, Request $request, ReclamationRepository $ReclamationRepository, SerializerInterface $serializerInterface, PaginatorInterface $paginator): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $reclamation = $ReclamationRepository->findAll();
        $reclamation = $paginator->paginate(
            $reclamation,
            $request->query->getInt('page', 1),
            2
        );
        return $this->render('reclamation/indexBack.html.twig', ['rec' => $reclamation,'user'=>$user]);
    }

    #[Route('/create', name: 'app_reclamation1')]
    public function ajouter(SessionInterface $session, UserRepository $userRepository,Request $request, ReclamationRepository $reclamationRepository): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contenu = $reclamation->getContenu(); // get the contenu from the Reclamation object
            $badWords = ['shit', 'fuck', 'Fuck off', 'piss off', 'bugger off', 'Bloody hell', 'Bastard', 'Bollocks', 'Motherfucker', 'Son of a bitch', 'Asshole', 'ass', 'va te faire foudre', 'nigga'];
            $cleanContenu = str_ireplace($badWords, '******', $contenu); // replace bad words with **

            $reclamation->setContenu($cleanContenu); // update the Reclamation object with the cleaned contenu
            $reclamationRepository->add($reclamation, true);

            return $this->redirectToRoute('read_front');
        }

        return $this->renderForm('reclamation/create.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
            'user' => $user,
        ]);
    }









    // /**
    //  * @Route("/{id}", name="app_reclamation_delete")
    //  */
    public function delete(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository, $id): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $reclamationRepository->remove($reclamation, true);
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }




    #[Route('/delete1/{id}', name: 'delete1')]
    public function remove(ManagerRegistry $doctrine, $id, ReclamationRepository $repo): Response
    {
        $objSupp = $repo->find($id);
        $em = $doctrine->getManager();
        $em->remove($objSupp);
        $em->flush();
        return $this->redirectToRoute('read_front');
    }




    #[Route('/update1/{id}', name: 'update')]
    public function update(SessionInterface $session, UserRepository $userRepository,Request $request, ManagerRegistry $doctrine, Reclamation $reclamation)
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);


        //pour créer un formulaire
        $form = $this->createForm(ReclamationType::class, $reclamation);
        //traiter la requete reçu par le formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && ($form->isValid())) {
            $em = $doctrine->getManager();
            $em->persist($reclamation);
            $em->flush();
            return $this->redirectToRoute('read_front');
        }

        return $this->render('reclamation/edit.html.twig', ['form' => $form->createView(),'user' => $user,]);
    }
    ////////////////mobile/////////////////////////////
    #[Route('/reclamation/afficheM', name: 'afficheMo')]
    public function show_mobile(ReclamationRepository $ReclamationRepository, SerializerInterface $serializerInterface)
    {
        $reclamation = $ReclamationRepository->findAll();
        $json = $serializerInterface->serialize($reclamation, 'json', ['groups' => 'reclamation']);

        return new JsonResponse($json, 200, [], true);
    }
    // $serializer = new Serializer([new ObjectNormalizer()]);
    //$formatted=$serializer->normalize($reclamation);
    //return new JsonResponse($formatted);


    #[Route('/reclamation/ajouteM', name: 'ajouteMo')]
    public function add_mobile(Request $request, SerializerInterface $serializer, EntityManagerInterface $em)
    {

        $reclamation = new Reclamation();
        $contenu = $request->query->get("contenu");
        $nom = $request->query->get("nom");
        $email = $request->query->get("email");
        $prenom = $request->query->get("prenom");
        $em = $this->getDoctrine()->getManager();

        $reclamation->setContenu($contenu);
        $reclamation->setNom($nom);
        $reclamation->setEmail($email);
        $reclamation->setPrenom($prenom);

        $em->persist($reclamation);
        $em->flush();

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($reclamation);
        return new JsonResponse($formatted);
    }
    #[Route("/reclamation/modifierM", name: "modifM")]

    public function update_mobile(Request $request, SerializerInterface $serializer): Response
    {
        $em = $this->getDoctrine()->getManager();
        $reclamation = $em->getRepository(Reclamation::class)->find($request->get('id'));

        if (!$reclamation) {
            throw $this->createNotFoundException('La réclamation avec l\'ID ' . $request->get('id') . ' n\'existe pas.');
        }

        $reclamation->setContenu($request->get('contenu'));
        $reclamation->setNom($request->get('nom'));
        $reclamation->setEmail($request->get('email'));
        $reclamation->setPrenom($request->get('prenom'));

        $em->flush();

        $jsonContent = $serializer->serialize($reclamation, 'json', ['groups' => 'reclamation']);
        return new Response('La réclamation a été modifiée avec succès : ' . $jsonContent);
    }

    #[Route("/reclamation/deleteM", name: "supprimeM")]


    public function delete_mobile(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        $id = $request->query->get("id");
        $entityManager = $this->getDoctrine()->getManager();
        $reclamationRepository = $entityManager->getRepository(Reclamation::class);
        $reclamation = $reclamationRepository->find($id);

        if ($reclamation !== null) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $formatted = $serializer->serialize($reclamation, 'json');

            return new Response($formatted);
        }



        return new Response(" Reclamation does not exist ");
    }




    //////////////////////pdf//////////////////////////
    #[Route('/reclamation/pdf', name: 'generator_service')]
    public function pdfService(): Response
    {
        $reclamation = $this->getDoctrine()
            ->getRepository(Reclamation::class)
            ->findAll();



        $html = $this->renderView('pdf/index.html.twig', ['reclamation' => $reclamation]);
        $pdfGeneratorService = new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
    }

    ////////////////////////////Trie///////////////////////////
    //Email
    #[Route('/reclamation/email', name: 'orderE')]
    public function order_By_Prix(SessionInterface $session, UserRepository $userRepository,Request $request, ReclamationRepository $reclamationRepository): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);
        //list of students order By Dest
        $ReclamationByEmail = $reclamationRepository->order_By_Email();

        return $this->render('reclamation/index.html.twig', [
            'rec' => $ReclamationByEmail,
            'user' => $user,
        ]);
    }
    //Nom
    #[Route('/reclamation/nom', name: 'orderN')]
    public function orderByName(SessionInterface $session, UserRepository $userRepository,ReclamationRepository $repository)
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $reclamations = $repository->orderByName();

        return $this->render('reclamation/index.html.twig', [
            'rec' => $reclamations,
            'user' => $user,
        ]);
    }
    ///////////////////////////recherche/////////////////////////
    #[Route("/reclamation/search", name: "SearchR")]
    public function searchReclamation(Request $request, ReclamationRepository $reclamationRepository)
    {
        try {
            $searchTerm = $request->request->get('searchTerm');
            if ($searchTerm === null) {
                return new Response("y a pas de searchTerm");
            }

            $reclamation = $reclamationRepository->findBySearchTerm($searchTerm);
            $foundReclamations = false;
            if (empty($reclamation)) {
                return new Response("pas de reclamation");
            }
            $response = [];

            foreach ($reclamation as $Otherec) {
                $foundReclamations = true;
                $response[] = [
                    'id' => $Otherec->getId(),
                    'contenu' => $Otherec->getContenu(),
                    'nom' => $Otherec->getNom(),
                    'email' => $Otherec->getEmail(),
                    'prenom' => $Otherec->getPrenom(),
                ];
            }
            if (!$foundReclamations) {
                return new JsonResponse("no Reclamations found");
            } else {
                return new JsonResponse($response);
            }
        } catch (\Exception $e) {
            return new Response("Erreur: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Dompdf\Dompdf;
use Dompdf\Options;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\DateTime;



class EventController extends AbstractController
{

    // FRONT :
    #[Route('/front/frontlist', name: 'event_list')]
    public function ListEvents(SessionInterface $session, UserRepository $userRepository, EventsRepository $repository): Response
    {
        try {
            // get logged in user from session
            $userId = $session->get('user')['idUser'];
            $user = $userRepository->find($userId);
            $events = $repository->findAll();
        } catch (\Throwable $th) {
            $user = null;
            $events = $repository->findAll();
        }


        return $this->render('front/frontlist.html.twig', ['events' => $events,'user'=>$user]);
    }





    // list backadmin :
    #[Route('/event/list', name: 'event_list_back')]
    public function ListEventsback(SessionInterface $session, UserRepository $userRepository, EventsRepository $repository): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $events = $repository->findAll();
        return $this->render('event/index.html.twig', ['events' => $events, 'user' => $user]);
    }


    #[Route('/back/add', name: 'event_add', methods: ['GET', 'POST'])]
    public function addEvent(SessionInterface $session, UserRepository $userRepository,Request $request, ManagerRegistry $doctrine): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $event = new Events();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file instanceof UploadedFile) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads'), $filename);
                $event->setImage($filename); // set image value to $event, not $form->getData()
            } else {
                // Set default image filename here
                $event->setImage('default.jpg');
            }


            $entityManager = $doctrine->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_list_back');
        }
        return $this->render('event/add.html.twig', [
            'form' => $form->createView(),
             'user' => $user
        ]);
    }






    #[Route('modifier/{id}', name: 'modifier')]

    public function modifier(SessionInterface $session, UserRepository $userRepository,Request $request, ManagerRegistry $doctrine, Events $events): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $form = $this->createForm(EventType::class, $events);
        $form->handleRequest($request);
        if ($form->IsSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();
            if ($file instanceof UploadedFile) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads'), $filename);
                $events->setImage($filename); // set image value to $event, not $form->getData()
            }


            $em = $doctrine->getManager();
            //persist=ajouter
            $em->persist($events);
            //flush=pish
            $em->flush();
            return $this->redirectToRoute('event_list_back', []);
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    #[Route('supprimer/{id}', name: 'supprimer')]

    public function supprimer($id, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $events = $doctrine->getRepository(Events::class);
        $events =  $events->find($id);
        $em->remove($events);
        $em->flush();
        return $this->redirectToRoute('event_list_back');
    }

    //-----------------------------------PDF:-------------------------------------------
    #[Route('/events/pdf', name: 'event_pdf')]
    public function pdf(EventsRepository $eventsRepository): Response
    {
        // Configuration de dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // initialisation pdf
        $dompdf = new Dompdf($pdfOptions);

        //retreive the events data from the database
        $events = $eventsRepository->findAll();

        //render the eventst from the database
        $html = $this->renderView('front/pdf.html.twig', [
            'events' => $events,
        ]);
        //load html
        $dompdf->loadHtml($html);

        //setup the paper format
        $dompdf->setPaper('A4', 'Portrait');

        //render pdf as html content
        $dompdf->render();


        //save pdf as listeevenements pdf
        $dompdf->stream("listedesevenements.pdf");

        //output to browser
        return new Response('', 200, [
            'Content-Type' => 'applcation/pdf',
        ]);
    }
    //-----------------------------------  Trie par date ordre desc:-------------------------------------------

    #[Route('/events/tridesc', name: 'event_order_by_date_desc')]
    public function orderEventsByDateDesc(EventsRepository $eventsRepository): Response
    {
        $events = $eventsRepository->orderEventsByDateDesc();

        return $this->render('front/frontlist.html.twig', [
            'events' => $events,
        ]);
    }

    //-----------------------------------  Trie par date ordre ASC:-------------------------------------------

    #[Route('/events/triasc', name: 'event_order_by_date_asc')]
    public function orderEventsByDateASC(EventsRepository $eventsRepository): Response
    {
        $eventsasc = $eventsRepository->orderEventsByDateAsc();

        return $this->render('front/frontlist.html.twig', [
            'events' => $eventsasc,
        ]);
    }







    //-----------------------------------Json:-------------------------------------------
    //FRONT Liste event:

    #[Route('/frontjson', name: 'event_list_json')]
    public function ListEventsjson(EventsRepository $repository, SerializerInterface $SerializerInterface): Response
    {
        $events = $repository->findAll();
        $json = $SerializerInterface->serialize($events, 'json', ['groups' => 'event']);

        return new JsonResponse($json, 200, [], true);
    }


    //ADD event json :
    #[Route('/addjson', name: 'event_add_json')]
    public function addEventjson(Request $request, ManagerRegistry $doctrine, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $events = new Events();

        $title = $request->query->get("title");
        $description = $request->query->get("description");
        $date = $request->query->get("date");
        $location = $request->query->get("location");
        $image = $request->query->get("image");


        if ($title !== null) {
            $events->setTitle($title);
        }
        if ($description !== null) {
            $events->setDescription($description);
        }

        if ($date !== null) {
            $events->setDate(new \DateTime($date));
        }

        if ($location !== null) {
            $events->setLocation($location);
        }
        if ($image !== null) {
            $events->setImage($image);
        }

        $em->persist($events);
        $em->flush();

        $formatted = $serializer->normalize($events, null, ['groups' => 'event']);
        return new JsonResponse($formatted);
    }
}

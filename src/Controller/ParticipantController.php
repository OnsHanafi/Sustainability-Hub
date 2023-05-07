<?php

namespace App\Controller;

use App\Classe\mail;
use App\Entity\Events;
use App\Entity\Participant;


use App\Form\ParticipantType;
use App\Form\ParticipeventType;
use App\Repository\EventsRepository;
use App\Repository\ParticipantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant/add/{id}', name: 'participant_add')]
    public function addEvent(SessionInterface $session, UserRepository $userRepository, Request $request, ManagerRegistry $doctrine, EventsRepository $eventsRepository, $id): Response
    //    public function add($id,Request $request, EntityManagerInterface $entityManager,ManagerRegistry $doctrine)

    {
        try {
            // get logged in user from session
            $userId = $session->get('user')['idUser'];
            $user = $userRepository->find($userId);
        } catch (\Throwable $th) {
            $user = null;
        }


        $notification = null;

        $event = $eventsRepository->find($id);
        $participant = new Participant();
        $form = $this->createForm(ParticipeventType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $participant->setEvents($event);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($participant);
            $entityManager->flush();

            $mail = new mail();
            $content = "Bienvenue a sustainability hub  " . $participant->getName();
            $mail->send($participant->getEmail(), $participant->getName(), "Merci Pour votre participation  ", $content);



            return $this->redirectToRoute('event_list');
        }
        return $this->render('participant/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'user' => $user
        ]);
    }

    #[Route('/participant/list', name: 'participant_list')]
    public function ListEventsback(SessionInterface $session, UserRepository $userRepository, ParticipantRepository $repository): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        //        $participant=$repository->findAll();
        $participants = $repository->createQueryBuilder('p')
            ->join('p.Events', 'e')
            ->select('p.name, p.phone_number,p.email,e.title')
            ->getQuery()
            ->getResult();
        foreach ($participants as $participant) {
            echo $participant['name'] . ', ' . $participant['phone_number'] . ', ' . $participant['email'] . ', ' . $participant['title'] . '<br>';
        }

        return $this->render('participant/participantlist.html.twig', ['participants' => $participants, 'user' => $user]);
    }



    //-----------------------------------PDF:-------------------------------------------
    #[Route('/participant/pdf', name: 'event_pdf_participant')]
    public function pdfListeParticipant(ParticipantRepository $participantRepository): Response
    {
        // Configuration de dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // initialisation pdf
        $dompdf = new Dompdf($pdfOptions);

        //retreive the events data from the database
        $participant = $participantRepository->findAll();

        //render the eventst from the database
        $html = $this->renderView('participant/pdf.html.twig', [
            'Participant' => $participant,
        ]);
        //load html
        $dompdf->loadHtml($html);

        //setup the paper format
        $dompdf->setPaper('A4', 'Portrait');

        //render pdf as html content
        $dompdf->render();


        //save pdf as listedeparticipant pdf
        $dompdf->stream("listedeparticipant.pdf");

        //output to browser
        return new Response('', 200, [
            'Content-Type' => 'applcation/pdf',
        ]);
    }




    //-----------------------------------Search:-------------------------------------------


    //    #[Route('/search-participants/{searchText}', name: 'search-participants')]
    //
    //    public function searchParticipants(ParticipantRepository $repository, string $searchText): Response
    //    {
    //        $participants = $repository->searchParticipants($searchText);
    //
    //        return $this->render('/participant/participantlist.html.twig', [
    //            'participants' => $participants,
    //        ]);
    //    }

}

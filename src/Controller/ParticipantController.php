<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Participant;


use App\Form\ParticipantType;
use App\Repository\EventsRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant/add', name: 'participant_add')]
   public function addEvent(Request $request,ManagerRegistry $doctrine): Response
//    public function add($id,Request $request, EntityManagerInterface $entityManager,ManagerRegistry $doctrine)

    {
        $participant=new Participant();
//         $form =$doctrine->getRepository(Events::class);
        $form = $this->createForm(ParticipantType::class,$participant);
        $form->handleRequest($request);
//
//        $eventsRepository = $doctrine->getRepository(Events::class);
//        $events = $eventsRepository->find($id);
//
//        $participant->setEvents($events);
//        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($participant);
            $entityManager->flush();


            return $this->redirectToRoute('event_list');
        }
        return $this->render('participant/new.html.twig',[
            'form'=> $form->createView(),
        ]);
    }

        #[Route('/participant/list', name: 'participant_list')]
    public function ListEventsback(ParticipantRepository $repository): Response
    {
        $participant=$repository->findAll();
        return $this->render('participant/participantlist.html.twig',['participant'=>$participant]);
    }
}

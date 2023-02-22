<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Participant;


use App\Form\ParticipantType;
use App\Form\ParticipeventType;
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
    #[Route('/participant/add/{id}', name: 'participant_add')]
   public function addEvent(Request $request,ManagerRegistry $doctrine,EventsRepository $eventsRepository,$id): Response
//    public function add($id,Request $request, EntityManagerInterface $entityManager,ManagerRegistry $doctrine)

    {
        $event=$eventsRepository->find($id);
        $participant=new Participant();
        $form = $this->createForm(ParticipeventType::class,$participant);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $participant->setEvents($event);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($participant);
            $entityManager->flush();


            return $this->redirectToRoute('event_list');
        }
        return $this->render('participant/new.html.twig',[
            'form'=> $form->createView(),
            'event'=> $event
        ]);
    }

        #[Route('/participant/list', name: 'participant_list')]
    public function ListEventsback(ParticipantRepository $repository): Response
    {
//        $participant=$repository->findAll();
        $participants = $repository->createQueryBuilder('p')
            ->join('p.Events', 'e')
            ->select('p.name, p.phone_number, e.title')
            ->getQuery()
            ->getResult();
        foreach ($participants as $participant){
            echo $participant['name'] . ', ' . $participant['phone_number'] . ', ' . $participant['title'] . '<br>';
        }

        return $this->render('participant/participantlist.html.twig',['participants'=>$participants]);
    }
}

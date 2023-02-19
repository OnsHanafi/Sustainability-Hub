<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event/list', name: 'event_list')]
    public function ListEvents(EventsRepository $repository): Response
    {
        $events=$repository->findAll();
        return $this->render('event/list.html.twig',['events'=>$events]);

    }


    #[Route('/event/add', name: 'event_add')]
    public function addEvent(Request $request,ManagerRegistry $doctrine): Response
    {
        $event=new Events();
        $form = $this->createForm(EventType::class,$event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_list');
        }
        return $this->render('event/add.html.twig',[
            'form'=> $form->createView(),
        ]);
    }

    #[Route('/event/edit{id}', name: 'event_edit')]
    public function editEvent(Request $request,Events $events,ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(EventType::class, $events);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->render('event/edit.html.twig', [
                'event' => $events,
                'form' => $form->createView(),
            ]);
        }
    }


    #[Route('/event/delete{id}', name: 'event_delete')]
    public function deleteEvent(Request $request,Events $events,ManagerRegistry $doctrine): Response
    {
        if($this->isCsrfTokenValid('delete'.$events->getId(),$request->request->get('_token')))
            //isCsrfTokenValid for security CSRF is a type of security to make sure form cannot be faked by a third party
        {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($events);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_list');
    }

}

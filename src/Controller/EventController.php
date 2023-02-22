<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class EventController extends AbstractController
{

    // FRONT :
    #[Route('/front/frontlist', name: 'event_list')]
    public function ListEvents(EventsRepository $repository): Response
    {
        $events=$repository->findAll();
        return $this->render('front/frontlist.html.twig',['events'=>$events]);
    }

    // list backadmin :
    #[Route('/event/list', name: 'event_list_back')]
    public function ListEventsback(EventsRepository $repository): Response
    {
        $events=$repository->findAll();
            return $this->render('event/index.html.twig',['events'=>$events]);
    }


    #[Route('/back/add', name: 'event_add', methods: ['GET', 'POST'])]
    public function addEvent(Request $request,ManagerRegistry $doctrine): Response
    {
        $event=new Events();
        $form = $this->createForm(EventType::class,$event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $form->get('image')->getData();
            if ($file instanceof UploadedFile) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads'), $filename);
                $event->setImage($filename); // set image value to $event, not $form->getData()
            }


            $entityManager = $doctrine->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_list_back');
        }
        return $this->render('event/add.html.twig',[
            'form'=> $form->createView(),
        ]);
    }






    #[Route('modifier/{id}', name: 'modifier')]

    public function modifier(Request $request , ManagerRegistry $doctrine,Events $events): Response
    {

        $form = $this->createForm(EventType::class, $events);
        $form->handleRequest($request);
        if ($form ->IsSubmitted()&& $form->isValid()) {

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
            return $this->redirectToRoute('event_list_back', [
            ]);
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
        ]);

    }


    #[Route('supprimer/{id}', name: 'supprimer')]

    public function supprimer($id , ManagerRegistry $doctrine): Response
    {
        $em=$doctrine->getManager();
        $events =$doctrine->getRepository(Events::class);
        $events =  $events->find($id);
        $em->remove($events);
        $em->flush();
        return $this->redirectToRoute('event_list_back');

    }

}

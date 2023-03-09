<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/reclamation/reponse')]
class ReponseController extends AbstractController
{
    #[Route('/', name: 'app_reponse_index')]
  
            public function index()
            {
             return $this->redirectToRoute('app_ajoute');
         }
        
    

   

         #[Route('/new/{id}', name: 'app_ajoute')]
         public function new(Request $request, ReponseRepository $reponseRepository,ManagerRegistry $doctrine,$id): Response
         {
             $reponse = new Reponse();
             $form = $this->createForm(ReponseType::class, $reponse);
             $form->handleRequest($request);
     
             if ($form->isSubmitted() && $form->isValid()) {
                 $reponseRepository->add($reponse, true);
                 $reponse = new Reponse();
                 $reponse = $reponseRepository->find($id);
                 // $reclamation->setEtat(1 );
                 $em = $doctrine->getManager();
                 $em->flush();
                 $reponseRepository->sms();
                 $this->addFlash('danger', 'reponse envoyée avec succées');
                 return $this->redirectToRoute('app_affiche1');
                 
             }
     
             return $this->renderForm('reponse/new.html.twig', [
                 'reponse' => $reponse,
                 'form' => $form
                 //'reponses'=>$reponse
             ]);
         }

    #[Route('/show', name: 'app_affiche')]
    public function read2(ReponseRepository $ReponseRepository ):Response
    {
        $reponse=$ReponseRepository->findAll();
        
        return $this->render('reponse/index.html.twig',['reponses'=>$reponse]);
    }



    #[Route('/showBack', name: 'app_affiche1')]
    public function afficher(ReponseRepository $ReponseRepository ):Response
    {
        $reponse=$ReponseRepository->findAll();
        
        return $this->render('reponse/index2.html.twig',['reponses'=>$reponse]);
    }



    #[Route('/update2/{id}', name: 'app_modif')]
    
    public function update(Request $request,ManagerRegistry $doctrine,Reponse $reponse)
    {
     //pour créer un formulaire
     $form=$this->createForm(ReponseType::class,$reponse);
     //traiter la requete reçu par le formulaire
     $form->handleRequest($request);
 if ($form->isSubmitted()&&($form->isValid()))
 {$em=$doctrine->getManager();
     $em->persist($reponse);
 $em-> flush();
 
 return $this->redirectToRoute('app_affiche1');
 }
 
 return $this->render('reponse/edit.html.twig', ['form'=>$form->createView()]);
 
    }

    #[Route('/delete/{id}', name: 'app_supprime')]
    
    public function remove(ManagerRegistry $doctrine,$id,ReponseRepository $repo):Response
    {
$objSupp=$repo->find($id);
$em=$doctrine->getManager();
$em->remove($objSupp);
$em->flush();
return $this->redirectToRoute('app_affiche1');
    }
     //SMS
    /* #[Route('/traiter/{id}', name: 'participer')]
     function Traiter(ReponseRepository $reponseRepository, $id, Request $request, ManagerRegistry $doctrine)
     {
     
         $reclamation = new Reponse();
         $reclamation = $reponseRepository->find($id);
         // $reclamation->setEtat(1 );
         $em = $doctrine->getManager();
         $em->flush();
         $reponseRepository->sms();
         $this->addFlash('danger', 'reponse envoyée avec succées');
         return $this->redirectToRoute('app_affiche1');
     
     }*/
}

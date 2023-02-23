<?php

namespace App\Controller;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ReclamationController extends AbstractController
{
   
    #[Route('/reclamation', name: 'app_reclamation')]
   public function index()
    {
     return $this->redirectToRoute('app_reclamation1');
 }
 
     
    #[Route('/readFront', name: 'read_front')]
    public function readFront(ReclamationRepository $ReclamationRepository ):Response
    {
        $reclamation=$ReclamationRepository->findAll();
        
        return $this->render('reclamation/index.html.twig',['rec'=>$reclamation]);
    }

     
    #[Route('/readBack', name: 'liste')]
    public function read(ReclamationRepository $ReclamationRepository ):Response
    {
        $reclamation=$ReclamationRepository->findAll();
        
        return $this->render('reclamation/indexBack.html.twig',['rec'=>$reclamation]);
    }

    #[Route('/create', name: 'app_reclamation1')]
    public function ajouter(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamationRepository->add($reclamation, true);

            return $this->redirectToRoute('liste');
        }

        return $this->renderForm('reclamation/create.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    

    /**
     * @Route("/{id}", name="app_reclamation_delete")
     */
    
    public function delete(Request $request, Reclamation $reclamation, ReclamationRepository $reclamationRepository,$id): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $reclamationRepository->remove($reclamation, true);
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }



   
    #[Route('/delete1/{id}', name: 'delete1')]
    public function remove(ManagerRegistry $doctrine,$id,ReclamationRepository $repo):Response
    {
$objSupp=$repo->find($id);
$em=$doctrine->getManager();
$em->remove($objSupp);
$em->flush();
return $this->redirectToRoute('liste');
    }



   
    #[Route('/update1/{id}', name: 'update')]
    public function update(Request $request,ManagerRegistry $doctrine,Reclamation $reclamation)
    {
     //pour créer un formulaire
     $form=$this->createForm(ReclamationType::class,$reclamation);
     //traiter la requete reçu par le formulaire
     $form->handleRequest($request);
 if ($form->isSubmitted()&&($form->isValid()))
 {$em=$doctrine->getManager();
     $em->persist($reclamation);
 $em-> flush();
 return $this->redirectToRoute('read_front');
 }
 
 return $this->render('reclamation/edit.html.twig', ['form'=>$form->createView()]);
 
    }
     
}


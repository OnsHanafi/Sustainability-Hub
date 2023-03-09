<?php

namespace App\Controller;
use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;

use App\Notification\NouveauCompteNotification;

use Knp\Component\Pager\PaginatorInterface; 

use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\PdfGeneratorService;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ServiceController extends AbstractController



{




    /**********************Notification*********************** */


    /********************************************** */
    #[Route('/service/showService', name: 'app_service')]
    public function index( SessionInterface $session, UserRepository $userRepository, PaginatorInterface $paginator,Request $request): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $service= $this->getDoctrine()
        ->getRepository(Service::class)
        ->findAll();

        $service= $paginator->paginate(
            $service,
            $request->query->getInt('page', 1),6
            
            );

     return $this->render('Service/index.html.twig', ['service' => $service , 'user'=>$user]);
    }

    #[Route('/service/serviceFront', name: 'app_serviceFront')]
    public function Front(SessionInterface $session, UserRepository $userRepository,PaginatorInterface $paginator,Request $request): Response
    {   

        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $service= $this->getDoctrine()
        ->getRepository(Service::class)
        ->findAll();
        $service= $paginator->paginate(
            $service,
            $request->query->getInt('page', 1),6
            
            );
      return $this->render('Service/ServiceFront.html.twig', ['service' => $service,'user'=>$user]);
    }
    #[Route('/service/serviceCreate', name: 'create_service')]
    public function addService(SessionInterface $session, UserRepository $userRepository,Request $request ): Response
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        $user = $userRepository->find($userId);

        $service= new service();
        $form = $this->createForm(ServiceType::class,$service);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {//$file = $service->getImage();
            $file = $form->get('image')->getData();
            $filename = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('uploads'),$filename);
            $service->setImage($filename);
          
        
            $em=$this->getDoctrine()->getManager();
            $em->persist($service);
            $em->flush();

            return $this->redirectToRoute('app_service');

        }

        return $this->render('Service/ajouter.html.twig',['form'=>$form->createView(),'user'=>$user]);
    }
    #[Route('/serviceDelete/{id}', name: 'delete_service')]

public function Supprimer($id){
    $entityManager = $this->getDoctrine()->getManager();
        $service= $entityManager->getRepository(Service::class)->find($id);

        if (!$service) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        }

        $entityManager->remove($service);
         $entityManager->flush();
    return $this->redirectToRoute('app_service');
}


#[Route('/serviceUpdate/{id}', name: 'update_service')]
public function update(SessionInterface $session, UserRepository $userRepository,Request $request,$id): Response
{
    // get logged in user from session
    $userId = $session->get('user')['idUser'];
    $user = $userRepository->find($userId);


    $entityManager = $this->getDoctrine()->getManager();
   

    $service= $entityManager->getRepository(Service::class)->find($id);

    $form=$this->createForm(ServiceType::class,$service);

    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid())
    {//$file = $service->getImage();
        $file = $form->get('image')->getData();
        $filename = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->getParameter('uploads'),$filename);
        $service->setImage($filename);
   
   
    $entityManager->persist($service);

    
    $entityManager->flush();
    return $this->redirectToRoute('app_service');
    }

   else{
    return $this->render("service/ajouter.html.twig", 
    [
          'form' => $form->createView(),
          'user' => $user,
          ]
 );
   }

}
#[Route('/serviceRech/{id}', name: 'rech_service')]
public function recherche(Request $request,$id): Response
{
    $entityManager = $this->getDoctrine()->getManager();
   

    $service= $entityManager->getRepository(Service::class)->find($id);
    return $this->render('Service/ServiceRech.html.twig', ['service' => $service]);
}
/***************************************PDF****************************************** */

#[Route('/pdf/service', name: 'generator_service')]
    public function pdfService(): Response
    { 
        $service= $this->getDoctrine()
        ->getRepository(Service::class)
        ->findAll();

   

        $html =$this->renderView('pdfService/index.html.twig', ['service' => $service]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
       
    }


    /*************************Statistique********************************** */
    #[Route('/service/statistique', name: 'stats')]
public function stat(SessionInterface $session, UserRepository $userRepository)
    {
        // get logged in user from session
        $userId = $session->get('user')['idUser'];
        if($userId){
            $user = $userRepository->find($userId);
        }
        

        $repository = $this->getDoctrine()->getRepository(Service::class);
        $service= $repository->findAll();

        $em = $this->getDoctrine()->getManager();


        $pr1 = 0;
        $pr2 = 0;


        foreach ($service as $service) {
            if ($service->getCategory() == "sondes")  :

                $pr1 += 1;
            else:

                $pr2 += 1;

            endif;

        }

        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable(
            [['Category', 'nom'],
                ['service de type sondes', $pr1],
                ['service ne sont pas de type sondes', $pr2],
            ]
        );
        $pieChart->getOptions()->setTitle('Catégories des services');
        $pieChart->getOptions()->setHeight(1000);
        $pieChart->getOptions()->setWidth(1400);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('green');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(30);

       

        return $this->render('service/stat.html.twig', ['piechart' => $pieChart,'user'=>$user]);
    }

/******************Map**************************** */
    

     #[Route('/mapM',name:'mapM')]
     
    public function Map()
    {

        return $this->render('service/map.html.twig');
    }
    
/***************************Like &Dsilike******************************************** */


#[Route('/dislike/{id}', name: 'dislike_service')]
public function dislike(Request $request, Service $service)
{
    $service->setDislike($service->getDislike() + 1);
    $this->getDoctrine()->getManager()->flush();
 
    return $this->redirectToRoute('app_serviceFront');
}

#[Route('/like/{id}', name: 'like_service')]
public function like(Request $request, Service $service)
{
    $service->setLikes($service->getLikes() + 1);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirectToRoute('app_serviceFront');

}
/*****************************************************************************Recherche */
/*#[Route("/search", name: "searchService")]
    public function searchService(Request $request, serviceRepository $serviceRepository)
    {
        try {
            $searchTerm = $request->request->get('searchTerm');
            if ($searchTerm === null) {
                return new Response("y a pas de searchTerm");
            }
    
            $service = $serviceRepository->findBySearchTerm($searchTerm);
            $foundService = false;
            if (empty($service)) {
                return new Response("pas de service");
            }
            $response = [];
    
            foreach ($service as $Otherec) {
                $foundService = true;
                $response[] = [
                    'id' => $Otherec->getId(),
                    'image' => $Otherec->getImage(),
                    'nom' => $Otherec->getNom(),
                    'categorie' => $Otherec->getCategory(),
                    'localisation' => $Otherec->getLocalisation(),
                    'description' => $Otherec->getDescription(),
                    
                ];
            }
            if (!$foundService) {
                return new JsonResponse("no Service found");
            } else {
                return new JsonResponse($response);
            }
        } catch (\Exception $e) {
            return new Response("Erreur: " . $e->getMessage());
        }
    }



/*********************************************************************** */
//Email
#[Route('/service/orderTitre', name: 'orderTitre')]
    public function order_By_Titre(Request $request,ServiceRepository $serviceRepository): Response
    {
//list of students order By Dest
        $serviceByTitre= $serviceRepository->order_By_titre();

        return $this->render('service/serviceFront.html.twig', [
            'service' =>  $serviceByTitre,
        ]);
    }
    //Nom
    #[Route('/service/orderLocalisatio', name: 'orderLocalisation')]
    public function orderByLocalisation(ServiceRepository $repository)
    {
        $service = $repository->orderByLocalisation();
    
        return $this->render('service/serviceFront.html.twig', [
            'service' => $service,
        ]);
    }
}

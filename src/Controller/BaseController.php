<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    #[Route('/base', name: 'app_base')]
    public function index(): Response
    {
        return $this->render('Base.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }
    #[Route('/baseBack', name: 'app_base')]
    public function indx(): Response
    {
        return $this->render('post/index_back.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }

}

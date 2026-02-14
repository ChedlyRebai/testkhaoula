<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{
    #[Route('/reclamations', name: 'reclamation_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('front/reclamations/list.html.twig');
    }
}

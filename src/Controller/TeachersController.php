<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TeachersController extends AbstractController
{
    #[Route('/teachers', name: 'app_teachers')]
    public function index(): Response
    {
        return $this->render('teachers/index.html.twig', [
            'controller_name' => 'TeachersController',
        ]);
    }
}

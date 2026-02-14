<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'forum_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('front/forum/list.html.twig');
    }
}

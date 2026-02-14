<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/evenements', name: 'event_list', methods: ['GET'])]
    public function list(EventRepository $repository): Response
    {
        $events = $repository->findAllEnabled();

        return $this->render('front/events/list.html.twig', [
            'events' => $events,
        ]);
    }
}

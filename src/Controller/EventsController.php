<?php

namespace App\Controller;

use App\Repository\CalendarEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventsController extends AbstractController
{
    #[Route('/evenements', name: 'app_events')]
    public function index(CalendarEventRepository $repository): Response
    {
        return $this->render('events/index.html.twig', [
            'events' => $repository->findBy([], ['startAt' => 'ASC']),
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home_events')]
final class HomeController extends AbstractController
{
    #[Route(name: 'app_home_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }



    #[Route('/{id}', name: 'app_home_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('home/index.html.twig', [
            'event' => $event,
        ]);
    }



}

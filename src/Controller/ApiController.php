<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;
use App\Entity\Event;

#[Route('/manapi')]
final class ApiController extends AbstractController
{
    #[Route('/events', name: 'app_api')]
    public function index(EventRepository $eventRepository, CategorieRepository $categorieRepository): Response
    {
        $data = [];
        foreach ($eventRepository->findAll() as $event) {
            $data[] = [
                'id' => $event->getId(),
                'tittre' => $event->getTitle(),
                'description' => $event->getDescription(),
                'date' => $event->getDate()->format('d/m/y'),
                'heure' => $event->getDate()->format('H:i'),
                'lieu' => $event->getLocation()->getName(),
                'catégorie' => $this->getCategories($event),
            ];
        }
        return $this->json($data);
    }

    #[Route('/events/{id}', name: 'app_api_event_show')]
    public function show(Event $event): Response
    {
        $data = [
            'id' => $event->getId(),
            'tittre' => $event->getTitle(),
            'description' => $event->getDescription(),
            'date' => $event->getDate()->format('d/m/y'),
            'heure' => $event->getDate()->format('H:i'),
            'lieu' => $event->getLocation()->getName(),
            'catégorie' => $this->getCategories($event),
            'billets' => $this->getTicketTypes($event),
        ];
        return $this->json($data);
    }

    #[Route('/categories', name: 'app_api_categories')]
    public function categories(CategorieRepository $categorieRepository): Response
    {
        $data = [];
        foreach ($categorieRepository->findAll() as $categorie) {
            $data[] = [
                'id' => $categorie->getId(),
                'nom' => $categorie->getName(),
            ];
        }
        return $this->json($data);
    }

    #[Route('/locations', name: 'app_api_locations')]
    public function locations(LocationRepository $locationRepository): Response
    {
        $data = [];
        foreach ($locationRepository->findAll() as $location) {
            $data[] = [
                'id' => $location->getId(),
                'nom' => $location->getName(),
                'rue' => $location->getStreet(),
                'numero' => $location->getNumber(),
                'codePostal' => $location->getPostcode(),
                'ville' => $location->getCity(),
            ];
        }
        return $this->json($data);
    }

    private function getCategories(Event $event)
    {
        $cat = [];
        foreach ($event->getCategories() as $categorie) {
            array_push($cat, $categorie->getName());
        }
        return $cat;
    }

    private function getTicketTypes(Event $event)
    {
        $types = [];
        foreach ($event->getTickettypes() as $ticketType) {
            array_push($types, [
                'label' => $ticketType->getLabel(),
                'prix' => $ticketType->getPrice(),
                'maxBillet' => $ticketType->getMaxTicket(),
            ]);
        }
        return $types;
    }
}

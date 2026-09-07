<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\EventRepository;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;
use App\Entity\Event;

#[Route('/api')]
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

    private function getCategories(Event $event)
    {
        $cat = [];
        foreach ($event->getCategories() as $categorie) {
            array_push($cat, $categorie->getName());
        }
        return $cat;
    }
}

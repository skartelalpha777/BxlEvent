<?php

namespace App\Service;

use App\Repository\TicketTypeRepository;
use Symfony\Component\HttpFoundation\RequestStack;


class CartService
{

    private RequestStack $requestStack;
    private TicketTypeRepository $ticketTypeRepository;


    function __construct(RequestStack $requestStack, TicketTypeRepository $ticketTypeRepository)
    {
        $this->requestStack = $requestStack;
        $this->ticketTypeRepository = $ticketTypeRepository;
    }

    public  function addToCart(int $ticketTypeId, int $quantity): void
    {

        //recuperation de la session
        $session = $this->requestStack->getSession();

        // Récupère le panier actuel ou initialise un tableau vide
        $cart = $session->get('cart', []);

        // Incrémente la quantité si l'article existe déjà, sinon l'ajoute
        if (!empty($cart[$ticketTypeId])) {
            $cart[$ticketTypeId] = $quantity;
        } else {
            $cart[$ticketTypeId] = $quantity;
        }
        if ($quantity <= 0) {
            unset($cart[$ticketTypeId]);
        }

        $session->set('cart', $cart);
    }
}

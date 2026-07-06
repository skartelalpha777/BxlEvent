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

   public  function addToCart( int $ticketTypeId, int $quantity): void
    {
     


    }
}

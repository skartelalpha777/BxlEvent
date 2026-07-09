<?php

namespace App\Service;

use App\Repository\TicketTypeRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Répresente le panier qui  est stocké dans la session
 */
class CartService
{

    private RequestStack $requestStack;
    private TicketTypeRepository $ticketTypeRepository;

    /**
     * Summary of __construct ils s'agit du constructeur qui initialise les deux attribut ci-dessous
     * @param RequestStack $requestStack répresente la session
     * @param TicketTypeRepository $ticketTypeRepository contient les different type de tickets
     */
    function __construct(RequestStack $requestStack, TicketTypeRepository $ticketTypeRepository)
    {
        $this->requestStack = $requestStack;
        $this->ticketTypeRepository = $ticketTypeRepository;
    }


    /**
     * Ajoute/incrémente (utilisé par add())
     * @param int $ticketTypeId réprsente l'identifiant du type de ticket
     * @param int $quantity répresente la quantité
     * @return void ne retourne rien
     */
    public  function addToCart(int $ticketTypeId, int $quantity): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $cart[$ticketTypeId] = ($cart[$ticketTypeId] ?? 0) + $quantity;
        $session->set('cart', $cart);
    }
    /**
     * Fixe une quantité précise (utilisé par alterQuantity() et remove())
     * @param int $ticketTypeId répresente l'identifiant du type de ticket
     * @param int $quantity répresente la quantité
     * @return void ne retourne rien
     */
    public function setQuantity(int $ticketTypeId, int $quantity): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        if ($quantity <= 0) {
            unset($cart[$ticketTypeId]);
        } else {
            $cart[$ticketTypeId] = $quantity;
        }
        $session->set('cart', $cart);
    }

    /**
     * permet d'obtenir le panier
     * @return array{quantity: mixed, ticketType: object[]} return un tableau cléf=>valeur
     * qui contient des objects ticketType ainsi que la quantité
     */
    public function getCart(): array

    {
        $cartWithData = [];
        $cart = $this->requestStack->getSession()->get('cart', []);

        foreach ($cart as $id => $quantity) {
            $ticketType = $this->ticketTypeRepository->find($id);
            if ($ticketType) {
                $cartWithData[] = [
                    'ticketType' => $ticketType,
                    'quantity' => $quantity
                ];
            }
        }
        return $cartWithData;
    }

    /**
     * Permet d'avoir le prix total du panier
     * @return float le prix total
     */
    public function getTotal(): float
    {
        $cart = $this->getCart();
        $cartTotalPrice = 0;
        foreach ($cart as $item) {
            $cartTotalPrice += $item['ticketType']->getPrice() * $item['quantity'];
        }
        return $cartTotalPrice;
    }

    /**
     * Pemet de vider le panier
     * @return void ne retourne rien
     */
    public function clearCart(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }

    /**
     * Permet d'obtenir les frais de service
     * @return float les frais de service
     */
    public function getServicefee()
    {
        return 2.0;
    }
}

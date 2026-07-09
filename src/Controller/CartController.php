<?php

namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\Request;

#[Route('/cart')]
final class CartController extends AbstractController
{

    #[Route(name: 'app_cart_index')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getCart(),
            'subTotal' => $cartService->getTotal(),
            'serviceFee' => $cartService->getServicefee(),
            'total' => $cartService->getTotal() + $cartService->getServicefee(),
        ]);
    }
    /**
     * Permet d'ajouter un ticket au panier a partir de la page de tickets d'un evenement
     */
    #[Route('{id}/add', name: 'app_cart_add', methods: ['POST', 'GET'])]
    public function add(Event $event, CartService $cartService, Request $request): Response
    {

        $tickets = $request->request->all('tickets');

        if (empty($tickets)) {
            $this->addFlash('notice', 'Aucun ticket n\'a été sélectionné.');
            return $this->redirect($request->headers->get('referer'));
        }

        foreach ($tickets as $id => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity > 0) {
                $cartService->addToCart((int)$id, $quantity);
            }
        }

        $this->addFlash('success', 'le ou les tickets selectionés ont bien été ajoutés à votre panier.');

        return $this->redirectToRoute('app_event_tickets', ['id' => $event->getId()]);
    }


    #[Route('/increaseQuaity/{ticketTypeId}', name: 'app_cart_alter_quantity')]
    public function alterQuantity(int $ticketTypeId,  CartService $cartService, Request $request): Response
    {
        $submittedToken = $request->getPayload()->get('token');
        if ($this->isCsrfTokenValid('alter-cart', $submittedToken)) {
            $quantity = $request->request->get('quantity');
            if ($quantity > 10) {
                $this->addFlash('error', 'Vous ne pouvez pas avoir plus de 10 tickets par commande.');
                return $this->redirectToRoute('app_cart_index');
            }
            $cartService->setQuantity($ticketTypeId, $quantity);
            return $this->redirectToRoute('app_cart_index');
        }
        $this->addFlash('error', 'Erreur lors de la modification du panier.');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->setQuantity($id, 0);

        $this->addFlash('success', 'L\'article a bien été supprimé du panier.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/clear', name: 'app_cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clearCart();

        $this->addFlash('success', 'Le panier a été vidé.');

        return $this->redirectToRoute('app_cart_index');
    }
}

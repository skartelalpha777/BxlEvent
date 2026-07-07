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

    #[Route('{id}/add', name: 'app_cart_add', methods: ['POST', 'GET'])]
    public function add(Event $event, CartService $cartService, Request $request): Response
    {

        $tickets = $request->request->all('tickets');

        if (empty($tickets)) {
            $this->addFlash('notice', 'Aucun ticket n\'a été sélectionné.');
            return $this->redirect($request->headers->get('referer'));
        }

        foreach ($tickets as $id => $quantity) {

            $cartService->addToCart((int)$id, (int)$quantity);
        }

        $this->addFlash('success', 'le ou les tickets selectionés ont bien été ajoutés à votre panier.');

        return $this->redirectToRoute('app_event_tickets', ['id' => $event->getId()]);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->addToCart($id, 0);

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

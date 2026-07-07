<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CartService;

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

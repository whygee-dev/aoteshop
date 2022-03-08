<?php

namespace App\Controller;

use App\Entity\Product;
use App\Outils\Cart;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/mon-panier', name: 'cart')]
    public function index(Cart $cart, ManagerRegistry $registry): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getPopulatedCart()
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'add_to_cart')]
    public function add(Cart $cart, $id): Response
    {
        $cart->add($id);

        return $this->redirectToRoute('cart');
    }

    #[Route('/panier/supprimer', name: 'remove_cart')]
    public function remove(Cart $cart): Response
    {
        $cart->remove();

        return $this->redirectToRoute('products');
    }

    #[Route('/panier/supprimer/{id}', name: 'remove_from_cart')]
    public function delete($id, Cart $cart): Response
    {
        $cart->delete($id);

        return $this->redirectToRoute('cart');
    }

    #[Route('/panier/retirer/{id}', name: 'decrease_from_cart')]
    public function decrease($id, Cart $cart): Response
    {
        $cart->decrease($id);

        return $this->redirectToRoute('cart');
    }
}

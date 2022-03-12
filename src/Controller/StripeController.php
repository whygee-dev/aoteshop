<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Outils\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/stripe/create-session/{reference}', name: 'stripe_create_session')]
    public function index($reference, Cart $cart, EntityManagerInterface $em): Response
    {
        $YOUR_DOMAIN = $_ENV["APP_ENV"] === "dev" ? "http://localhost:8000" : "http://radiant-fortress-23554.herokuapp.com";
        $productsForStripe = [];

        $order = $em->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            return new JsonResponse(['error' => 'order']);
        }

        foreach ($order->getOrderLines()->getValues() as $item ) {
            $productObject = $em->getRepository(Product::class)->findOneByNom($item->getProduit());
            $productsForStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $item->getPrix(),
                    'product_data' => [
                        'name' => $item->getProduit(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$productObject->getIllustration()],
                    ],
                ],
                'quantity' => $item->getQuantite(),
            ];
        }

        $productsForStripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getPrixTransporteur(),
                'product_data' => [
                    'name' => $order->getNomTransporteur(),
                    'images' => ['https://media.istockphoto.com/vectors/street-food-truck-icon-logo-vector-id1197080827?k=20&m=1197080827&s=612x612&w=0&h=7KxF4Q0ZAktk8QhACblwhA61RhqmyhvvSm0goclT71Y='],
                ],
            ],
            'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51KQgFiHJ4pNVljxgNrg9k5v5I1GzkcpwWotbmFjHyA7eTRlMn3DxlzetVdaC3VIIyPbCIbFS3GTLWNmeZnjXH0SI004nxURjZu ');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [$productsForStripe],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);

        $em->flush();

        return new JsonResponse(['id' => $checkout_session->id]);
    }
}

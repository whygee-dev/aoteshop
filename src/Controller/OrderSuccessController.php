<?php

namespace App\Controller;

use App\Entity\Order;
use App\Outils\Cart;
use App\Outils\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    #[Route('/commande/merci/{stripeSessionId}', name: 'order_success')]
    public function index($stripeSessionId, EntityManagerInterface $em, Cart $cart): Response
    {
        $order = $em->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if (!$order->getPaye()) {
            $order->setPaye(true);
            $em->flush();
            $cart->remove();

            $mailer = new Mailer();
            $mailer->send($order->getUser()->getEmail(), $order->getUser()->getFullName(), "AOT ESHOP - Merci pour votre commande", "Confirmation de la commande n°" . $order->getReference(),
                "Bonjour ".$order->getUser()->getFullName().",<br/> <br/>Votre commande a bien été prise en compte, rendez-vous sur votre espace membre pour suivre le statut de votre commande.");

        } else {
            return $this->redirectToRoute('account');
        }


        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}

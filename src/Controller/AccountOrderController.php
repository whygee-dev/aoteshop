<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountOrderController extends AbstractController
{
    #[Route('/compte/commandes', name: 'account_order')]
    public function index(EntityManagerInterface $em): Response
    {
        $orders = $em->getRepository(Order::class)->findSuccessOrders($this->getUser());

        return $this->render('account/order.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/compte/commandes/{reference}', name: 'account_show_order')]
    public function show($reference, EntityManagerInterface $em): Response
    {
        $order = $em->getRepository(Order::class)->findOneByReference($reference);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_order');
        }

        return $this->render('account/show_order.html.twig', [
            'order' => $order
        ]);
    }
}

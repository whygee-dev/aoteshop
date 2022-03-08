<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Form\OrderType;
use App\Outils\Cart;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/commande', name: 'order')]
    public function index(Cart $cart, Request $request): Response
    {
        if (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('account_add_address');
        }

        $form = $this->createForm(OrderType::class, null, ['user' => $this->getUser()]);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getPopulatedCart()
        ]);
    }

    #[Route('/commande/recapitulatif', name: 'order_recap', methods: 'POST')]
    public function add(Cart $cart, Request $request, ManagerRegistry $registry): Response
    {
        $form = $this->createForm(OrderType::class, null, ['user' => $this->getUser()]);

        $form->handleRequest($request);

        $em = $registry->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $carriers = $form->get('transporteurs')->getData();
            $delivry = $form->get('addresses')->getData();
            $delivryString = $delivry->getPrenom().' '.$delivry->getNom();
            $delivryString .= '<br/>'.$delivry->getTelephone();

            if ($delivry->getSociete()) {
                $delivryString .= '<br/>'.$delivry->getCompany();
            }

            $delivryString.= '<br/>'.$delivry->getAdresse();
            $delivryString.= '<br/>'.$delivry->getCodePostal().' '.$delivry->getVille();
            $delivryString.= '<br/>'.$delivry->getPays();

            $date = new \DateTime();
            $order = new Order();
            $order->setReference($date->format('dmY').'-'.uniqid());
            $order->setUser($this->getUser());
            $order->setDateCreation($date);
            $order->setNomTransporteur($carriers->getNom());
            $order->setPrixTransporteur($carriers->getPrix());
            $order->setAdresseLivraison($delivryString);
            $order->setPaye(false);

            $em->persist($order);

            foreach ($cart->getPopulatedCart() as $item ) {
                $orderLine = new OrderLine();
                $orderLine->setOrder($order);
                $orderLine->setProduit($item['product']->getNom());
                $orderLine->setQuantite($item['quantity']);
                $orderLine->setPrix($item['product']->getPrix());
                $orderLine->setTotal($item['product']->getPrix() * $item['quantity']);
                $em->persist($orderLine);
            }

            $em->flush();


            return $this->render('order/add.html.twig', [
                'cart' => $cart->getPopulatedCart(),
                'carrier' => $carriers,
                'delivry' => $delivryString,
                'reference' => $order->getReference()
            ]);

        }

        return $this->redirectToRoute('cart');
    }
}

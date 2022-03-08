<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Outils\Cart;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAddressController extends AbstractController
{
    #[Route('/compte/adresses', name: 'account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig', [

        ]);
    }

    #[Route('/compte/ajouter-une-adresse', name: 'account_add_address')]
    public function add(Request $request, ManagerRegistry $registry, Cart $cart): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());

            $registry->getManager()->persist($address);
            $registry->getManager()->flush();

            if ($cart->get()) {
                return $this->redirectToRoute('order');
            }

            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/modifier-une-adresse/{id}', name: 'account_update_address')]
    public function update($id, Request $request, ManagerRegistry $registry): Response
    {
        $address= $registry->getRepository(Address::class)->findOneById($id);

        if (!$address || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_address');
        }

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registry->getManager()->flush();

            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/supprimer-une-adresse/{id}', name: 'account_delete_address')]
    public function delete($id, ManagerRegistry $registry): Response
    {
        $address= $registry->getRepository(Address::class)->findOneById($id);

        if ($address && $address->getUser() == $this->getUser()) {
            $registry->getManager()->remove($address);
            $registry->getManager()->flush();
        }

        return $this->redirectToRoute('account_address');
    }
}

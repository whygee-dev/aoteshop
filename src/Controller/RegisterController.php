<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Outils\Mailer;
use Doctrine\Persistence\ManagerRegistry;
use MongoDB\Driver\Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'register')]
    public function index(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($hasher->hashPassword($user,$user->getPassword()));

            $doctrine = $doctrine->getManager();

            if ($doctrine->getRepository(User::class)->findByEmail($user->getEmail())) {
                $this->addFlash('error', 'Un compte avec l\'email fourni existe déjà');

                return $this->render('register/index.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $doctrine->persist($user);
            $doctrine->flush();

            $mailer = new Mailer();
            $mailer->send($user->getEmail(), $user->getFullName(), "Bienvenue à AOT ESHOP", "Confirmation d'inscription",
                "Bonjour ".$user->getFullName().",<br/> <br/>Votre compte a bien été créé, rendez-vous sur votre espace membre pour gérer vos informations et passer commande.");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

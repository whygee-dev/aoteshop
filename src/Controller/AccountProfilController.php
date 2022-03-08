<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountProfilController extends AbstractController
{
    #[Route('/compte/profil', name: 'account_profil')]
    public function index(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher): Response
    {
        $notificationError = null;
        $notificationSuccess = null;
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $old_password = $form->get('old_password')->getData();

            if ($hasher->isPasswordValid($user, $old_password)) {
                $new_password = $form->get('new_password')->getData();
                $password = $hasher->hashPassword($user, $new_password);

                $user->setPassword($password);
                $user->setNom($form->get('nom')->getData());
                $user->setPrenom($form->get('prenom')->getData());

                $doctrine = $doctrine->getManager();
                $doctrine->flush();
                $notificationSuccess = "Vos données ont été mises à jour";
            } else {
                $notificationError = "Votre mot de passe actuel est incorrect";
            }
        }

        return $this->render('account/profil.html.twig',
            ['form' => $form->createView(), 'notificationError' => $notificationError, 'notificationSuccess' => $notificationSuccess ]);
    }
}

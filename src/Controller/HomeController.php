<?php

namespace App\Controller;

use App\Entity\Header;
use App\Entity\Product;
use App\Outils\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em): Response
    {
        $products = $em
                    ->getRepository(Product::class)
                    ->findByALaUne(1, ['nom' => 'DESC']);
        $headers = $em->getRepository(Header::class)->findAll();

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'headers' => $headers
        ]);
    }
}

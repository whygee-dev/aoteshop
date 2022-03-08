<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\SearchType;
use App\Outils\Search;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/nos-produits', name: 'products')]
    public function index(EntityManagerInterface $em, Request $req): Response
    {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $products = $em->getRepository(Product::class)->findWithSearch($search);
        } else {
            $products = $em->getRepository(Product::class)->findBy(['disponible' => true], ['nom' => 'DESC']);
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }

    #[Route('/produit/{slug}', name: 'product')]
    public function show($slug, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        $products = $em
            ->getRepository(Product::class)
            ->findByALaUne(1);

        if (!$product) {
            return $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products
        ]);
    }
}

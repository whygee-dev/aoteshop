<?php

namespace App\Outils;


use App\Entity\CartLine;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class Cart
{
    private $requestStack, $security, $registry;

    public function __construct(RequestStack $requestStack, Security $security, ManagerRegistry $registry) {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->registry = $registry;
    }

    public function getPopulatedCart()
    {
        $cartPopulated = [];
        $cart = $this->get();

        if ($cart) {
            foreach ($cart as $id => $quantity) {
                $product = $this->registry->getRepository(Product::class)->findOneById($id);

                if (!$product) {
                    $this->delete($id);
                }

                $cartPopulated[] = [
                    'id' => $id,
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartPopulated;
    }

    private function addToSession($id, $quantity = 1) {
        $cart = $this->requestStack->getSession()->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = $quantity;
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    private function addToDb($id, $quantity = 1) {
        $em = $this->registry->getManager();
        $cart = $this->registry->getRepository(\App\Entity\Cart::class)->findOneBy(['user' => $this->security->getUser()]);

        $product = $em->getRepository(Product::class)->find($id);

        if (!$product) return;

        $newCartLine = new CartLine();
        $newCartLine->setProduct($product);

        if ($cart) {
            $lines = $cart->getCartLines();

            $newCartLine->setCart($cart);

            if ($lines) {
                $lines = $lines->toArray();
                $found = null;

                foreach ($lines as $key => $value) {
                    if ($value->getProduct()->getId() == $id) {
                        $found = $value;
                    }
                }

                if ($found) {
                    $newCartLine = $found;
                    $newCartLine->setQuantity($found->getQuantity() + 1);
                } else {
                    $newCartLine->setQuantity($quantity);
                }
            } else {
                $newCartLine->setQuantity($quantity);
            }
        } else {
            $cart = new \App\Entity\Cart();
            $cart->setUser($this->security->getUser());

            $newCartLine->setCart($cart);
            $newCartLine->setQuantity($quantity);

            $em->persist($cart);
        }

        $em->persist($newCartLine);
        $em->flush();

    }

    public function add($id, $quantity = 1, $persistSession = true)
    {
        if ($this->security->getUser()) {
            $this->addToDb($id, $quantity);
        }

        $persistSession && $this->addToSession($id, $quantity);
    }

    public function get()
    {
        if ($this->security->getUser()) {
            $cart = $this->registry->getRepository(\App\Entity\Cart::class)->findOneBy(['user' => $this->security->getUser()]);

            if ($cart) {
                $lines =  $cart->getCartLines()->toArray();
                $res = [];

                foreach ($lines as $line) {
                    $res[$line->getProduct()->getId()] = $line->getQuantity();
                }

                return $res;
            }
        }

        return $this->requestStack->getSession()->get('cart', []);
    }

    public function remove()
    {
        if ($this->security->getUser()) {
            $em = $this->registry->getManager();

            $em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->update()
                ->set('u.cart', ':cart')
                ->setParameter('cart', null)
                ->where('u.id = :userId')
                ->setParameter('userId', $this->security->getUser()->getId())
                ->getQuery()->getResult();

            $em->getRepository(\App\Entity\Cart::class)
                ->createQueryBuilder('c')
                ->delete()
                ->where('c.user = :userId')
                ->setParameter('userId', $this->security->getUser()->getId())
                ->getQuery()->getResult();
        }

        return $this->requestStack->getSession()->remove('cart');
    }

    public function delete($id)
    {
        $cart = $this->requestStack->getSession()->get('cart');
        $cartId = $this->registry->getRepository(\App\Entity\Cart::class)->findOneBy(['user' => $this->security->getUser()]);

        unset($cart[intval($id)]);
        $this->requestStack->getSession()->set('cart', $cart);

        if ($this->security->getUser() && $cartId) {
            $em = $this->registry->getManager();

            $em->getRepository(CartLine::class)
                ->createQueryBuilder('c')
                ->delete()
                ->where('c.cart = :cartId AND c.product = :productId')
                ->setParameter('cartId', $cartId->getId())
                ->setParameter('productId', $id)
                ->getQuery()
                ->getResult();
        }

        return $cart;
    }

    public function decrease($id)
    {
        $cart = $this->requestStack->getSession()->get('cart');

        if ($cart) {
            if ($cart[$id] == 1) {
                return $this->delete($id);
            } else {
                $cart[$id]--;
                $this->requestStack->getSession()->set('cart', $cart);
            }
        }

        if ($this->security->getUser()) {
            $cartId = $this->registry->getRepository(\App\Entity\Cart::class)->findOneBy(['user' => $this->security->getUser()]);

            if ($cartId) {
                $em = $this->registry->getManager();

                $em->getRepository(CartLine::class)
                    ->createQueryBuilder('c')
                    ->update()
                    ->set('c.quantity', 'c.quantity - 1')
                    ->where('c.cart = :cartId AND c.product = :productId')
                    ->setParameter('cartId', $cartId->getId())
                    ->setParameter('productId', $id)
                    ->getQuery()
                    ->getResult();
            }
        }
    }
}
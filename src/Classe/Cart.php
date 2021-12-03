<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private SessionInterface $session;
    private EntityManagerInterface $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function getCart()
    {
        return $this->session->get('cart');
    }

    public function add($id)
    {
        $cart = $this->session->get('cart');

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function remove()
    {
        $this->session->remove('cart');
    }

    public function delete($id)
    {
        $cart = $this->session->get('cart');

        unset($cart[$id]);

        return $this->session->set('cart', $cart);
    }

    public function removeItem($id)
    {
        $cart = $this->session->get('cart');

        $cart[$id]--;

        if ($cart[$id] === 0) {
            unset($cart[$id]);
        }

        return $this->session->set('cart', $cart);
    }

    public function addItem($id)
    {
        $cart = $this->session->get('cart');

        $cart[$id]++;

        return $this->session->set('cart', $cart);
    }

    #[ArrayShape(['cart' => "array", 'total' => "float|int"])] public function getCartProducts(): array
    {
        $cartFinal = [];
        $total = 0;

        if (self::getCart()) {
            foreach (self::getCart() as $id => $quantity) {
                $productObj = $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);

                if (!$productObj) {
                    $this->delete($id);
                    continue;
                }

                $total += $productObj->getPrice() * $quantity;

                $cartFinal[] = [
                    'product' => $productObj,
                    'quantity' => $quantity,
                ];
            }
        }

        return [
            'cart' => $cartFinal,
            'total' => $total
        ];
    }
}
<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/cart', name: 'cart')]
    public function index(Cart $cart): Response
    {
        $fullCart = $cart->getCartProducts();

        return $this->render('cart/index.html.twig', [
            'cart' => $fullCart['cart'],
            'total' => $fullCart['total']
        ]);
    }

    #[Route('/cart/add/{id}', name: 'add_to_cart')]
    public function add(Cart $cart, $id): Response
    {
        $cart->add($id);
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/remove', name: 'remove_cart')]
    public function remove(Cart $cart): Response
    {
        $cart->remove();
        return $this->redirectToRoute('products');
    }

    #[Route('/cart/delete/{id}', name: 'delete_line_from_cart')]
    public function delete(Cart $cart, $id): Response
    {
        $cart->delete($id);
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/decrease/{id}', name: 'remove_one_item_from_cart')]
    public function removeItem(Cart $cart, $id): Response
    {
        $cart->removeItem($id);
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/increase/{id}', name: 'add_one_item_to_cart')]
    public function addItem(Cart $cart, $id): Response
    {
        $cart->addItem($id);
        return $this->redirectToRoute('cart');
    }
}

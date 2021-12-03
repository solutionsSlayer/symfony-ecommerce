<?php

namespace App\Controller\Account;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountOrderController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/account/orders', name: 'account_order')]
    public function index(): Response
    {
        $user = $this->getUser();
        $orders = $this->entityManager->getRepository(Order::class)->findBySuccess($user);

        return $this->render('account/orders.html.twig', [
            'orders' => $orders
        ]);
    }
}

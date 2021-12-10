<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    /**
     * @throws ApiErrorException
     */
    #[Route('/order/create-session/{reference}', name: 'stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // SET API KEY
        Stripe::setApiKey($_SERVER['STRIPE_API_SECRET_KEY']);
        $reference = $request->attributes->get('reference');
        $domain = $request->server->get('SYMFONY_APPLICATION_DEFAULT_ROUTE_URL');
        $success = $domain.'order/success';
        $cancelled = $domain.'order/cancelled';
        $productsForStripe = [];

        $order = $entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);

        foreach ($order->getOrderDetails() as $product) {
            $productsForStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getProduct()
                    ],
                    'unit_amount' => $product->getPrice(),
                ],
                'quantity' =>  $product->getQuantity(),
            ];
        }

        $productsForStripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $order->getCarrierName()
                ],
                'unit_amount' => $order->getCarrierPrice() * 1,
            ],
            'quantity' =>  1,
        ];

        $session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [
                $productsForStripe
            ],
            'mode' => 'payment',
            'success_url' => $success.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelled.'?session_id={CHECKOUT_SESSION_ID}'
        ]);

        $order->setStripeCheckoutId($session->id);
        $entityManager->persist($order);
        $entityManager->flush();

        return $this->redirect($session->url);
    }
}

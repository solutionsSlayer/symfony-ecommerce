<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/order', name: 'order')]
    public function index(Cart $cart): Response
    {
        if (!$cart->getCart()) {
            return $this->redirectToRoute('products');
        }

        if (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('account_add_address');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);


        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getCartProducts()
        ]);
    }

    #[Route('/order/{id}', name: 'order_show')]
    public function show($id): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['id' => $id]);

        if (!($order && $this->getUser() == $order->getUser())) {
            return $this->redirectToRoute('account');
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/order/overview', name: 'order_add', methods: 'POST')]
    public function add(Request $request, Cart $cart): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $totalOrder = 0;
            $totalProductsAmount = 0;

            $carriers = $form->get('carriers')->getData();
            $address = $form->get('address')->getData();
            $fullAddress = $address->getFirstname().' '.$address->getLastname();

            if ($address->getCompany()) {
                $fullAddress .= '<br/>' . $address->getCompany();
            }

            $fullAddress .= '<br/>' . $address->getAddress(). '<br/>' . $address->getCity();
            $fullAddress .= '<br/>' . $address->getCountry() . '<br/>' .$address->getPhone();

            $date = new \DateTimeImmutable();
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setReference($date->format('dmy').'-'.uniqid());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers->getName());
            $order->setCarrierPrice($carriers->getPrice() * 100);
            $order->setDeliuery($fullAddress);
            $order->setIsPaid(0);

            foreach ($cart->getCartProducts()['cart'] as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setOrderArchive($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);

                $this->entityManager->persist($orderDetails);
                $totalOrder = $product['product']->getPrice() * $product['quantity'];
                $totalProductsAmount .= ($product['product']->getPrice() / 100) * $product['quantity'];
            }

            $order->setTotal($totalOrder + $carriers->getPrice() * 100);
            $order->setProductsAmount(($totalProductsAmount * 100));
            $this->entityManager->flush();

            return $this->render('order/order_add.html.twig', [
                'cart' => $cart->getCartProducts()['cart'],
                'total' => $totalOrder,
                'carriers' => $carriers,
                'carrier_price' => $carriers->getPrice(),
                'address' => $address,
                'reference' => $order->getReference()
            ]);
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @throws ApiErrorException
     */
    #[NoReturn] #[Route('/order/success', name: 'order_success')]
    public function success(Cart $cart, Request $request): Response
    {
        Stripe::setApiKey($_SERVER['STRIPE_API_SECRET_KEY']);
        $session = Session::retrieve($request->get('session_id'));
        $customer = Customer::retrieve($session->customer);
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['stripeCheckoutId' => $request->get('session_id')]);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if (!$order->getIsPaid()) {
            $order->setIsPaid(1);
            $cart->remove();
            $this->entityManager->flush();
        }

        return $this->render('order/order_succeed.html.twig', [
            'session' => $session,
            'customer' => $customer,
            'order' => $order
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    #[Route('/order/cancelled', name: 'order_cancelled')]
    public function cancel(): Response
    {
        return $this->render('order/order_cancelled.html.twig', [

        ]);
    }
}

<?php

namespace App\Controller\Account;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAddressController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/account/address', name: 'account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig');
    }

    #[Route('/account/add/address', name: 'account_add_address')]
    public function add(Cart $cart, Request $request): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $user = $this->getUser();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($user);
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            if ($cart->getCart()) {
                return $this->redirectToRoute('order');
            }

            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/add_address.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/account/edit/address/{id}', name: 'account_edit_address')]
    public function modify(Request $request, $id): Response
    {
        $address = $this->entityManager->getRepository(Address::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(AddressType::class, $address);

        if (!$address || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_address');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/edit_address.html.twig', [
            'form' => $form->createView(),
            'address' => $address
        ]);
    }

    #[Route('/account/delete/address/{id}', name: 'account_delete_address')]
    public function delete($id): Response
    {
        $address = $this->entityManager->getRepository(Address::class)->findOneBy(['id' => $id]);

        if (!$address || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_address');
        }
        $this->entityManager->remove($address);
        $this->entityManager->flush();

        return $this->redirectToRoute('account_address');
    }
}

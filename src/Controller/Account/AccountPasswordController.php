<?php

namespace App\Controller\Account;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManger) {
        $this->entityManager = $entityManger; 
    }

    #[Route('/account/password', name: 'account_password')]
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $notification = null;
        $success = true;

        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($encoder->isPasswordValid($user, $form->get('old_password')->getData())) {
            $encodedPwd = $encoder->hashPassword($user, $form->get('new_password')->getData());
            $user->setPassword($encodedPwd);

            /**
            ** Persist is usefulll only on data creation.
            *! $this->entityManager->persist($user);
            */
            
            $this->entityManager->flush();
            $notification = 'Your password has been successfully changed.';
            } else {
                $success = false;
                $notification = 'You current password is wrong, try again..';
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notif' => $notification,
            'success' => $success
        ]);
    }
}

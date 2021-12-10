<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegisterController extends AbstractController
{
    private $entityManager;
    PRIVATE $urlGenerator;
    
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'register')]
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $verifyUserExist = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if (!$verifyUserExist) {
                $pwd = $user->getPassword();
                $pwd = $encoder->hashPassword($user, $pwd);
                $user->setPassword($pwd);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $mail->registration(
                    $user->getEmail(),
                    $user->getUsername(),
                    'Registration successfully completed.',
                    'Hello '.$user->getUsername().' '.$user->getLastname().', welcome on the ecommerce'
                );

                return $this->redirectToRoute('app_login');

            } else {

                $notification = "You already have a account, please login.";
            }
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notif' => $notification
        ]);
    }
}

<?php

namespace App\Controller;
use App\Classe\Mail;
use MailchimpTransactional;
use App\Entity\ResetPassword;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/reset/password', name: 'reset_password')]
    public function index(Request $request): Response
    {
        $email = $request->get('email');

        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            $date = new DateTimeImmutable();
            $reset_pwd = new ResetPassword();
            $reset_pwd->setUser($user);
            $reset_pwd->setCreatedAt($date);
            $reset_pwd->setToken(uniqid());

            $this->entityManager->persist($reset_pwd);
            $this->entityManager->flush();

            $mailchimp = new Mail();
            $mailchimp->pingMailchimp();
        }

        return $this->render('reset_password/index.html.twig', [
            'controller_name' => 'ResetPasswordController',
        ]);
    }
}

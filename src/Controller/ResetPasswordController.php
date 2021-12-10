<?php

namespace App\Controller;
use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
        // 1) On login page a link allow us to go on this page (first time no info => render reset-password template)
        // 2) Second time we will have the email of the user who has forgot his password, execute treatment.

        $email = $request->get('email');

        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            // Create new reset pwd with good data.
            $date = new DateTimeImmutable();
            $reset_pwd = new ResetPassword();
            $reset_pwd->setUser($user);
            $reset_pwd->setCreatedAt($date);
            $reset_pwd->setToken(uniqid());

            // Save in BDD.
            $this->entityManager->persist($reset_pwd);
            $this->entityManager->flush();

            // Build URL for reset the user password.
            $url = $this->generateUrl('update_password', [
                'token' => $reset_pwd->getToken()
            ]);

            $content = "Hello, ".$user->getFirstname().",<br><br>You have asked for reset your password, please click on the following link: <a href='".$url."'>reset-password</a>";

            // Send email to the user.
            $mail = new Mail();
            $mail->resetPassword(
                $user->getEMail(),
                $user->getFirstname().' '.$user->getLastname(),
                'Reset password',
                $content
            );
        } else {
            $this->addFlash('notice', "<span>'>This email is unknown.</span>");
        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/update/password/{token}', name: 'update_password')]
    public function updatePassword($token, Request $request, UserPasswordHasherInterface $encoder)
    {
        $now = new \DateTime();
        $reset_pwd = $this->entityManager->getRepository(ResetPassword::class)->findOneBy(['token' => $token]);

        if (!$reset_pwd) {
            return $this->redirectToRoute('reset_password');
        }

        $validityToken = $reset_pwd->getCreatedAt()->modify('+ 3 hour');

        if ($now > $validityToken) {
            $this->addFlash('notice', "<span>The request for reset your password has expired.</span>");
            return $this->redirectToRoute('reset_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->entityManager->getRepository(User::class)->find($reset_pwd->getUser()->getId());

            if (!$user) {
                $this->addFlash('notice', "<span>Something wrong try again later.</span>");
                return $this->redirectToRoute('reset_password');
            }

            $newPwd = $form->get('new_password')->getData();
            $newPwd = $encoder->hashPassword($user,$newPwd);
            $user->setPassword($newPwd);

            $this->entityManager->flush();
            $this->addFlash('notice', "<span>The password has been successfully modified.</span>");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

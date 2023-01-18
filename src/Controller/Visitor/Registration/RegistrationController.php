<?php
namespace App\Controller\Visitor\Registration;


use App\Entity\User;
use App\Service\SendEmailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'visitor.registration.register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        TokenGeneratorInterface $tokenGenerator,
        SendEmailService $sendEmailService
    ): Response
    {

        if ($this->getUser()) 
        {
            return $this->redirectToRoute('visitor.welcome.index');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {

            // encode the plain password
            $password_hashed = $userPasswordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($password_hashed);

            // Generate the token
            $token_generated = $tokenGenerator->generateToken();
            $user->setTokenForEmailVerification($token_generated);

            // Generate the deadline for email validation
            $now = new \DateTimeImmutable('now');
            $expires_at = $now->add(new \DateInterval('P1D'));
            $user->setExpiresAt($expires_at);

            // Insert new user into table "user" in the database
            $entityManager->persist($user);
            $entityManager->flush();

            // send an email to user
            $sendEmailService->send([
                "sender_email"          => "medecine-du-monde@gmail.com",
                "sender_name"           => "Jean Dupont",
                "recipient_email"       => $user->getEmail(),
                "subject"               => "Vérification de votre compte sur le blog de Jean Dupont",
                "html_template"         => "email/email_verification.html.twig",
                "context"               => [
                    "user_id"       => $user->getId(),
                    "token"         => $user->getTokenForEmailVerification(),
                    "expires_at"    => $user->getExpiresAt()->format('d/m/Y H:i:s')
                ]
            ]);

            return $this->redirectToRoute('visitor.welcome.index');
        }

        return $this->render('pages/visitor/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/register/email-verif/{id<\d+>}/{token}', name: 'visitor.registration.email_verif')]
    public function emailVerif(User $user, string $token, UserRepository $userRepository) : Response
    {
        if ( ! $user ) 
        {
            throw new AccessDeniedException();
        }

        if ( $user->isIsVerified() ) 
        {
            $this->addFlash('warning', "Votre compte a déjà été vérifié! Veuillez vous connecter.");
            return $this->redirectToRoute('visitor.authentication.login');
        }

        if ( empty($token) || empty($user->getTokenForEmailVerification()) || ($token !== $user->getTokenForEmailVerification()) ) 
        {
            throw new AccessDeniedException();
        }

        if ( new \DateTimeImmutable('now') > $user->getExpiresAt() ) 
        {
            $deadline = $user->getExpiresAt();
            $userRepository->remove($user, true);

            throw new CustomUserMessageAccountStatusException("Votre délai de vérification du compte est dépassé depuis le : $deadline. Veuillez vous réinscrire.");
        }


        $user->setIsVerified(true);
        $user->setVerifiedAt(new \DateTimeImmutable('now'));

        $user->setTokenForEmailVerification(null);
        $user->setExpiresAt(null);

        $userRepository->save($user, true);

        $this->addFlash("message", "Votre compte a bien été vérifié! Vous pouvez vous connecter.");

        return $this->redirectToRoute('visitor.welcome.index');
    }
}

<?php

namespace App\Controller;

use App\Dto\Request\CheckEmailByConfirmationCodeInputDto;
use App\Dto\Request\ConfirmationEmailInputDto;
use App\Dto\Request\SendConfirmationCodeInputDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,

            // OPTIONAL parameters to customize the login form:

            // the translation_domain to use (define this option only if you are
            // rendering the login template in a regular Symfony controller; when
            // rendering it from an EasyAdmin Dashboard this is automatically set to
            // the same domain as the rest of the Dashboard)
            'translation_domain' => 'admin',

            // the title visible above the login form (define this option only if you are
            // rendering the login template in a regular Symfony controller; when rendering
            // it from an EasyAdmin Dashboard this is automatically set as the Dashboard title)
            'page_title' => '<i class="fa fa-book"></i> Книга долгов',

            // the string used to generate the CSRF token. If you don't define
            // this parameter, the login form won't include a CSRF token
            'csrf_token_intention' => 'authenticate',

            // the URL users are redirected to after the login (default: '/admin')
            'target_path' => $this->generateUrl('admin'),

            // the label displayed for the username form field (the |trans filter is applied to it)
            'username_label' => 'Your email',

            // the label displayed for the password form field (the |trans filter is applied to it)
            'password_label' => 'Your password',

            // the label displayed for the Sign In form button (the |trans filter is applied to it)
            'sign_in_label' => 'Log in',

            // the 'name' HTML attribute of the <input> used for the username field (default: '_username')
            'username_parameter' => 'username',

            // the 'name' HTML attribute of the <input> used for the password field (default: '_password')
            'password_parameter' => 'password',
            ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/send-confirmation-code', name: 'app_send_confirmation_code', methods: ['POST'])]
    public function sendConfirmationCode(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, MailerInterface $mailer): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), SendConfirmationCodeInputDto::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], Response::HTTP_FORBIDDEN);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $dto->getUsername()]);

        $confirmationCode = rand(1000, 9999);

        if (!$user) {
            $user = new User();
            $user->setUsername($dto->getUsername());
            $user->setPassword(bin2hex(random_bytes(8)));
            $user->setRoles(['ROLE_GUEST']);
            $user->setConfirmationCode($confirmationCode);
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            $user->setConfirmationCode($confirmationCode);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $htmlContent = $this->renderView(
            'email/confirmation_code.html.twig',
            ['confirmationCode' => $confirmationCode]
        );

        $email = (new Email())
            ->from('info@business-control.kz')
            ->to($dto->getUsername())
            ->subject('Confirmation Code')
            ->text($htmlContent);

        $email->getHeaders()->addTextHeader('Content-Type', 'text/html');

        $mailer->send($email);

        return new JsonResponse(['username' => $dto->getUsername(), 'role' => 'ROLE_GUEST']);
    }

    #[Route(path: '/email-by-code', name: 'app_email_by_code', methods: ['POST'])]
    public function checkEmailByConfirmationCode(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), CheckEmailByConfirmationCodeInputDto::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], Response::HTTP_FORBIDDEN);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $dto->getUsername(), 'confirmationCode' => $dto->getCode()]);

        if (!$user) {
            return $this->json(['error' => 'User not found for the provided email'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['username' => $dto->getUsername(), 'code' => $dto->getCode()]);
    }

    #[Route(path: '/confirm', name: 'app_confirm_email', methods: ['POST'])]
    public function confirmationEmail(Request $request,
                                      EntityManagerInterface $entityManager,
                                      SerializerInterface $serializer,
                                      ValidatorInterface $validator,
                                      UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), ConfirmationEmailInputDto::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], Response::HTTP_FORBIDDEN);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $dto->getUsername(), 'confirmationCode' => $dto->getCode()]);

        if (!$user) {
            return $this->json(['error' => 'User not found for the provided email'], Response::HTTP_NOT_FOUND);
        }

        $user->setUsername($dto->getUsername());
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $dto->getPlainPassword()
        );
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);
        $user->setConfirmationCode(null);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['username' => $dto->getUsername(), 'role' => 'ROLE_USER']);
    }
}

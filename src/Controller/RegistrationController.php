<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Créer une nouvelle instance de l'utilisateur
        $user = new User();

        // Créer et traiter le formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe de l'utilisateur
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,  // L'objet User qui implémente PasswordAuthenticatedUserInterface
                    $form->get('plainPassword')->getData()  // Assurez-vous que 'plainPassword' est le champ correct
                )
            );

            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setProfilePicture('default.jpg');

            // Save the user
            $entityManager->persist($user);
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash('success', 'Inscription réussie !');

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire d'inscription
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
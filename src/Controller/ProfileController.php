<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

// Cette classe gère le profil de l'utilisateur connecté ainsi que les profils publics.
final class ProfileController extends AbstractController
{
    // Cette route est accessible via l'URL /profile/me/edit, et est nommeé app_profile_edit dans l'application.
    #[Route('/profile/me/edit', name: 'app_profile_edit')]
    // Cette fonction permet d'afficher et modifier les données personnelles de l'utilisateur connecté.
    public function editProfile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Grâce à AbstractController l'état de connexion de l'utilisateur est connu.
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder au profil');
        }
        // Création du formulaire à partir de l'objet $user.
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est valide, le mot de passe sera haché et stocké dans l'objet $user.
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Téléversement de l'image de profil, création d'un nom de fichier unique avec extension et enregistrement dans le dossier public.
            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('profile_images_directory'), $newFilename);
                    $user->setProfileImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'ajout de l'image.");
                }
            }
            // Enregistrement des modifications dans la base de données.
            $em->flush();
            $this->addFlash('success', 'Le profil a été mis à jour.');

            // Retour à la page du profil.
            return $this->redirectToRoute('app_profile_edit');
        }

        // Affichage du formulaire du profil.
        return $this->render('profile/edit.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    // URL du profil public avec le nom de route app_user_profile.
    #[Route('/user/{username}', name: 'app_user_profile')]
    // Cette fonction affiche le profil public d'un utilisateur.
    public function showUserProfile(UserRepository $userRepository, string $username ) : Response
    {
        // Recherche de l'utilisateur ciblé grâce à la méthode findOneBy et au nom d'utilisateur.
        $user = $userRepository->findOneBy(['username' => $username]);

        // Si l'utilisateur est introuvable, affichage d'un message d'erreur.
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }
        // Affichage du profil via le fichier public.html.twig et l'objet $user.
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}

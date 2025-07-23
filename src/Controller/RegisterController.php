<?php

namespace App\Controller;
// Chargement des composants et des fichier nécessaires pour la construction du contrôleur
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class RegisterController extends AbstractController
{
    // Le routing de la page d'inscription
    #[Route('/register', name: 'app_register')]
    // La méthode register permet la création et la validation du formulaire d'inscription
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, SluggerInterface $slugger): Response
    {
        // Création d'une nouvelle instance de user
        $user = new User();
        // Création du formulaire d'inscription lié à l'entité User
        $form = $this->createForm(RegisterFormType::class, $user);
        // Traitement de la requête HTTP
        $form->handleRequest($request);

        // Si le formulaire a été soumis et est valide
        if($form->isSubmitted() && $form->isValid()) {
            // Récupération du mot de passe brut
            $plainPassword = $form->get('password')->getData();
            // Hashage du mot de passe avant stockage
            $hashedPassword = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Gestion de l'upload du fichier image de profil
            $profileImage = $form->get('profileImage')->getData();

            // Si l'utilisateur a envoyé un image de profil
            if ($profileImage) {
                // On récupère le nom de fichier original sans l'extension.
                $originalFilename = pathinfo($profileImage->getClientOriginalName(), PATHINFO_FILENAME);
                // Transformation en nom 'safe (sans les caractères spéciaux)
                $safeFilename = $slugger->slug($originalFilename);
                // Création d'un nom de fichier unique avec une extension correcte
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profileImage->guessExtension();

                // Déplacement du fichier uploadé vers le dossier configuré.
                try {
                    $profileImage->move(
                        $this->getParameter('profile_image_directory'), // Le chemin configuré dans le framework.yaml
                         $newFilename
                    );
                    // Si une erreur survient lors de l'upload, message d'erreur et redirection
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'upload du photo de profil");
                    return $this->redirectToRoute('app_register');
                }
                // Sauvegarde du nom du fichier dans la base de données
                $user->setProfileImage($newFilename);
            }

            // Attribution du rôle par défaut, ROLE_USER
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new \DateTimeImmutable());
            $em->persist($user);
            $em->flush();

            // Message du succès et redirection vers la page de connexion
            $this->addFlash('success', 'Inscription réussi ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire n'est pas soumis ou contient des erreurs, affichage du formulaire.
        return $this->render('register/sidebar.html.twig', [
            'registerForm' => $form->createView(), // Vue du formulaire pour twig
        ]);
    }
}

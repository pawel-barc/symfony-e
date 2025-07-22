<?php

namespace App\Controller;

// Ensemble des composants nécessaires pour la construction du contrôleur
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// La class SecurityController hérite d'AbstractController, elle permet de gérer les routes, d'afficher le formulaire de connexion et
// de traiter les erreurs éventuelles
class SecurityController extends AbstractController
{
    // Définition de la route (URL) pour la page de connexion
    #[Route(path: '/login', name: 'app_login')]
    // La fonction login est exécutée lorsqu'un utilisateur accède à la page /login
    // Elle récupère les erreurs et le dernier identifiant saisi 
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, on peut le rediriger vers une autre page 
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // Récupère la dernière erreur par exemple Mot de passe incorrect
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupère le dérnière email de l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche le template twig du formulaire en y passant les variables 
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    // La méthode qui gère la déconnexion de l'utilisateur
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepte automatiquement la requête
        throw new \LogicException('Cette méthode peut rester vide');
    }
}

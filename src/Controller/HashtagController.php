<?php

namespace App\Controller;

// Import du repository qui permet d'interagir avec la table Hashtag en base
use App\Repository\HashtagRepository;

// Import de la classe de base pour les contrôleurs Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Import de la classe Response pour retourner une réponse HTTP
use Symfony\Component\HttpFoundation\Response;

// Import pour définir les routes via annotation/attribute
use Symfony\Component\Routing\Annotation\Route;

final class HashtagController extends AbstractController
{
    // Définition d'une route HTTP GET accessible via "/posts/hashtag"
    // Le nom interne de cette route est "app_hashtag"
    #[Route('/posts/hashtag', name: 'app_hashtag')]
    public function index(HashtagRepository $hashtagRepository): Response
    {
        // Utilisation du repository pour récupérer tous les hashtags en base de données
        $hashtags = $hashtagRepository->findAll();

        // Rendu du template Twig 'hashtag/index.html.twig' en lui passant la liste des hashtags
        // La variable 'hashtags' sera accessible dans la vue Twig
        return $this->render('hashtag/index.html.twig', [
            'hashtags' => $hashtags,
        ]);
    }
}

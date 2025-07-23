<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class SearchController extends AbstractController
{
    // Cette route renvoie une réponse JSON en fonction d'une requête AJAX.
    // Elle est accessible via l'URL /ajax/user-search, et est nommeé /ajax/user_search. 
    #[Route('/ajax/user-search', name: 'ajax_user_search')]
    // Cette fonction gère les requêtes AJAX de la bare de recherche, pour trouver les utilisateurs.
    public function search(Request $request, UserRepository $userRepository): JsonResponse
    {
        // Récupération de la valeur du paramètre 'q' depuis l'URL.
        // Si 'q' est vide ou absent, on utilise une chaîne vide par défaut.
        $query = $request->query->get('q', '');
        $users = [];
        // Si la requête n'est pas vide on recherche des utilisateurs dont le username contient la chaîne.
        if ($query) {
            $users = $userRepository->createQueryBuilder('u')
            ->where('u.username LIKE :query')
            ->orWhere('u.firstname LIKE :query')
            ->orWhere('u.lastname LIKE :query')
            ->setParameter('query', '%' . $query . '%')//Correspondance partielle
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
        }    
        // On transforme les objets User en tableau associatif (array) contenant le username, firstname et lastname.
        $result = array_map(fn(User $user) => [
            'username' => $user->getUsername(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname()

        ], $users);
        // On retourne une réponse JSON contenant les utilisateurs trouvés.
            return $this->json($result);
    }
}

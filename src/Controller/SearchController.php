<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    // Cette route est accessible via l'URL /user-search, et est nommeé user_search dans l'application. 
    #[Route('/user-search', name: 'user_search')]
    // Cette fonction gère la bare de recherche et pérmet de trouver les utilisateur.
    public function search(Request $request, UserRepository $userRepository): Response
    {
        // Récupération de la valeur du paramètre 'q' de la requête GET, ou chaine vide si non défini
        $query = $request->query->get('q', '');
        $users = [];
        // Si la requête n'est pas vide on cherche les utilisateurs dont le nom d'utilisateur correspond partielement à la requête.
        if ($query) {
            $users = $userRepository->createQueryBuilder('u')
            ->where('u.username LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
            // Si un seul utilisateur correspond à la recherche, on redirige automatiquement vers son profil public.
            if (count($users) === 1) {
                return $this->redirectToRoute('app_user_profile', [
                    'username' => $users[0]->getUsername()
                ]);
            }
        }
        // Si plusieurs utilisateurs sont trouvés ou si aucun ne l'est, on affiche la page des résultats.
        return $this->render('search/user_results.html.twig', [
            'users' => $users,
            'query' => $query
        ]);
    }
}

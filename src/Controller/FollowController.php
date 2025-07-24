<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follow;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

// Cette classe gère l'ajout des abonnements ainsi que l'accès aux listes abonnés et d'abonnements.
final class FollowController extends AbstractController
{
    // Route pour suivre un utilisateur.
    #[Route('/follow/{id}', name: 'user_follow', methods: ['POST'])]
    // Cette fonction permet à un utilisateur 
    public function follow(User $userToFollow, FollowRepository $followRepo, EntityManagerInterface $em): RedirectResponse
    {
        $currentUser = $this->getUser();

        // L'utilisateur doit être connecté et ne peut pas s'abonner à lui même.
        if (!$currentUser || $currentUser === $userToFollow) {
            $this->addFlash("error", "Impossible de suivre cet utilisateur");
            return $this->redirectToRoute('user_profile', ['username' => $userToFollow->getUsername()]);
        }

        // Vérifie si l'abonnement existe déjà
        $existingFollow = $followRepo->findOneBy([
            'follower' => $currentUser,
            'followed' => $userToFollow
        ]);

        // Si ce n'est pas le cas, crée un nouvel abonnement 
        if (!$existingFollow) {
            $follow = new Follow();
            $follow->setFollower($currentUser);
            $follow->setFollowed($userToFollow);
            $em->persist($follow);
            $em->flush();
        }

        // Redirige vers le profil public de l'utilisateur suivi.
        return $this->redirectToRoute('user_profile', [
            'username' => $userToFollow->getUsername()
        ]);
    }

    
}

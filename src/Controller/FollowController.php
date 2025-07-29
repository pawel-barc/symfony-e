<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follow;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;

// Cette classe gère les abonnements (suivre/désabonner) ainsi que l'affichage des abonnés et abonnements d'un utilisateur
final class FollowController extends AbstractController
{
    // Route pour suivre un utilisateur via JSON.
    #[Route('/api/follow/{username}', name: 'api_user_follow', methods: ['POST'])]
    public function follow(
        string $username,
        FollowRepository $followRepo,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse {
        $currentUser = $this->getUser();
        $userToFollow = $userRepository->findOneBy(['username' => $username]);

        if (!$userToFollow) {
            return $this->json([
                "success" => false,
                "message" => "Utilisateur introuvable"
            ], 404);
        }

        // L'utilisateur doit être connecté et ne peut pas s'abonner à lui-même.
        if (!$currentUser || $currentUser === $userToFollow) {
            return $this->json([
                "success" => false,
                "message" => "Impossible de suivre cet utilisateur"
            ], 400);
        }

        // Vérifie si l'abonnement existe déjà
        $existingFollow = $followRepo->findOneBy([
            'follower' => $currentUser,
            'followed' => $userToFollow
        ]);

        $followback = $followRepo->findOneBy([
                'follower' => $userToFollow,
                'followed' => $currentUser,
            ]) !== null;

        // Si ce n'est pas le cas, crée un nouvel abonnement
        if (!$existingFollow) {
            $follow = new Follow();
            $follow->setFollower($currentUser);
            $follow->setFollowed($userToFollow);
            $em->persist($follow);
            $em->flush();
        }

        return $this->json([
            "success" => true,
            "following" => true,
            "followback" => $followback,
            "username" => $userToFollow->getUsername(),
        ]);
    }

    // Route pour se désabonner d'un utilisateur via JSON.
    #[Route('/api/unfollow/{username}', name: 'api_user_unfollow', methods: ["DELETE"])]
    public function unfollow(
        string $username,
        EntityManagerInterface $em,
        FollowRepository $followRepo,
        UserRepository $userRepository
    ): JsonResponse {
        $currentUser = $this->getUser();
        $userToUnfollow = $userRepository->findOneBy(["username" => $username]);

        if (!$userToUnfollow) {
            return $this->json([
                "success" => false,
                "message" => "Utilisateur introuvable"
            ], 404);
        }

        if (!$currentUser || $currentUser === $userToUnfollow) {
            return $this->json([
                "success" => false,
                "message" => "Impossible de se désabonner de cet utilisateur"
            ], 400);
        }

        $existingFollow = $followRepo->findOneBy([
            'follower' => $currentUser,
            'followed' => $userToUnfollow,
        ]);

        $followback = $followRepo->findOneBy([
                'follower' => $userToUnfollow,
                'followed' => $currentUser,
            ]) !== null;

        if ($existingFollow) {
            $em->remove($existingFollow);
            $em->flush();
        }

        return $this->json([
            "success" => true,
            "following" => false,
            "followback" => $followback,
            "username" => $userToUnfollow->getUsername()
        ]);
    }

    // Liste des abonnés d'un utilisateur
    #[Route('/{username}/followers', name: 'user_followers')]
    public function followers(
        #[MapEntity(mapping: ['username' => 'username'])] User $user
    ): Response {
        return $this->render('follow/index.html.twig', [
            'user' => $user,
            'type' => 'followers',
        ]);
    }

    // Liste des abonnements d'un utilisateur
    #[Route('/{username}/following', name: 'user_following')]
    public function following(
        #[MapEntity(mapping: ['username' => 'username'])] User $user
    ): Response {
        return $this->render('follow/index.html.twig', [
            'user' => $user,
            'type' => 'following',
        ]);
    }
}

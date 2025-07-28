<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follow;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Cette classe gère les abonnements(suivre/désabonner) ainsi que l'affichage' des abonnées et abonnements d'un utilisateur
final class FollowController extends AbstractController
{
    // Route pour suivre un utilisateur via JSON.
    #[Route('/api/follow/{username}', name: 'api_user_follow', methods: ['POST'])]
    // Cette fonction permet à un utilisateur de s'abonner à un autre utilisateur
    public function follow(string $username, FollowRepository $followRepo, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $currentUser = $this->getUser();

        $userToFollow = $userRepository->findOneBy(['username' => $username]);

        if(!$userToFollow) {
            return $this->json([
                "success" => false,
                "message" => "Utilisateur introuvable"
            ], 404);
        }

        // L'utilisateur doit être connecté et ne peut pas s'abonner à lui même.
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

        // Retourne une réponse JSON avec succès
        return $this->json([
            "success" => true,
            "following" => true,
            "followback" => $followback,
            // "userId" => $userToFollow->getId(),
            "username" => $userToFollow->getUsername(),
        ]);
    }

    // Route pour se désabonner d'un utilisateur via JSON.
    #[Route('/api/unfollow/{username}', name: 'api_user_unfollow', methods: ["DELETE"])]

    // Cette fonction permet à un utilisateur de se désabonner d'un autre utilisateur
    public function unfollow(string $username, EntityManagerInterface $em, FollowRepository $followRepo, UserRepository $userRepository ) :JsonResponse
    {
        $currentUser = $this->getUser();

        $userToUnfollow = $userRepository->findOneBy([
            "username" => $username
        ]);

        if (!$userToUnfollow) {
            return $this->json([
                "success" => false,
                "message" => "Utilisateur introuvable"
            ], 404);
        }

        // L'utilisateur doit être connecté et ne peut pas se désabonner de lui même.
        if (!$currentUser || $currentUser === $userToUnfollow) {
            return $this->json([
                "success" => false,
                "message" => "Impossible de se désabonner de cet utilisateur"
            ], 400);
        }

        // Vérifie si l'abonnement existe déjà
        $existingFollow = $followRepo->findOneBy([
            'follower' => $currentUser,
            'followed' => $userToUnfollow,
        ]);

         $followback = $followRepo->findOneBy([
            'follower' => $userToUnfollow,
            'followed' => $currentUser,
        ]) !== null;


        // Si c'est le cas, supprime l'abonnement
        if ($existingFollow) {
            $em->remove($existingFollow);
            $em->flush();
        }

        // Retourne une réponse JSON avec succès
        return $this->json([
            "success" => true,
            "following" => false,
            "followback" => $followback,
            // "userId" => $userToUnfollow->getId(),
            "username" => $userToUnfollow->getUsername()
        ]);
    }

    // Cette fonction affiche la liste des abonnés d'un utilisateur
    #[Route('/{username}/followers', name: 'user_followers', methods: ["GET"])]
    public function followers( string $username, UserRepository $userRepo): Response
    {
        $user = $userRepo->findOneBy([
            'username' => $username,
        ]);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable");
        }
        return $this->render('follow/follow_list.html.twig', [
            'user' => $user,
            'type' => 'followers'
        ]);
    }


    // Cette fonction affiche la liste des abonnements d'un utilisateur
    #[Route('/{username}/following', name: 'user_following', methods: ["GET"])]
    public function following( string $username, UserRepository $userRepo): Response
    {
        $user = $userRepo->findOneBy([
            'username' => $username,
        ]);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable");
        }
        return $this->render('follow/follow_list.html.twig', [
            'user' => $user,
            'type' => 'following'
        ]);
    }

}

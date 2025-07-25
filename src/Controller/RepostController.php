<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Repost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepostController extends AbstractController
{
    #[Route('/post/{id}/repost', name: 'post_repost', methods: ['POST'])]
    public function repost(Post $post, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $existingRepost = $em->getRepository(Repost::class)->findOneBy([
            'user' => $user,
            'post' => $post
        ]);


        $repost = new Repost();
        $repost->setUser($user);
        $repost->setPost($post);
        $repost->setCreatedAt(new \DateTimeImmutable());

        $em->persist($repost);
        $em->flush();

        return $this->json([
            'success' => true,
            'repostsCount' => $post->getRepostsCount(),
            'message' => 'Post reposté avec succès.'
        ]);
    }

    #[Route('/post/{id}/unrepost', name: 'post_unrepost', methods: ['POST'])]
    public function unrepost(Post $post, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $repost = $em->getRepository(Repost::class)->findOneBy([
            'user' => $user,
            'post' => $post
        ]);

        if (!$repost) {
            return $this->json([
                'success' => false,
                'message' => 'Vous n\'avez pas reposté ce post.'
            ], 400);
        }

        try {
            $em->remove($repost);
            $em->flush();

            return $this->json([
                'success' => true,
                'repostsCount' => $post->getRepostsCount(),
                'message' => 'Repost annulé avec succès.'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du repost: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fonction de debug pour identifier les problèmes de repost
     */
    private function debugRepostError(Post $post, User $user, EntityManagerInterface $em)
    {
        $debugSteps = [];

        try {
            // 1. Vérification de l'utilisateur
            if (!$user) {
                throw new \RuntimeException('Utilisateur non connecté');
            }
            $debugSteps[] = 'Étape 1: Utilisateur OK (' . $user->getUsername() . ')';

            // 2. Vérification du post
            if (!$post) {
                throw new \RuntimeException('Post introuvable');
            }
            $debugSteps[] = 'Étape 2: Post OK (ID: ' . $post->getId() . ')';

            // 3. Vérification de l'entité Repost
            $repost = new Repost();
            $repost->setUser($user);
            $repost->setPost($post);
            $debugSteps[] = 'Étape 3: Entité Repost initialisée';

            // 4. Vérification de la persistance
            $em->persist($repost);
            $em->flush();
            $em->remove($repost); // On nettoie après le test
            $em->flush();
            $debugSteps[] = 'Étape 4: Persistance OK';

            return true;

        } catch (\Exception $e) {
            $debugSteps[] = 'ERREUR: ' . $e->getMessage();
            return [
                'steps' => $debugSteps,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}

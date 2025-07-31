<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class PostLikeController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    public function like(int $id, EntityManagerInterface $em): JsonResponse
    {
        $this->logger->info('Like endpoint called for post '.$id);

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            $this->logger->error('Unauthorized like attempt');
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        try {
            $post = $em->getRepository(Post::class)->find($id);
            if (!$post) {
                $this->logger->error('Post not found: '.$id);
                return new JsonResponse(['error' => 'Post not found'], 404);
            }

            $likeRepo = $em->getRepository(PostLike::class);
            $existingLike = $likeRepo->findOneBy([
                'post' => $post,
                'author' => $user
            ]);

            if ($existingLike) {
                $em->remove($existingLike);

                // Supprimer la notification associée au like si elle existe
                $notification = $em->getRepository(Notification::class)->findOneBy([
                    'sender' => $user,
                    'receiver' => $post->getAuthor(),
                    'type' => ['like'],
                    'entityId' => $post->getId() 

                ]);
                if ($notification) {
                    $em->remove($notification);
                }
                $isLiked = false;
                $this->logger->info('Like removed for post '.$id.' by user '.$user->getId());
            } else {
                $like = new PostLike();
                $like->setPost($post);
                $like->setAuthor($user);
                $em->persist($like);
                // Chaque "Like" génère une notification pour l'utilisateur destinataire
                if ($post->getAuthor() !== $user) {
                    $notification = new Notification();
                    $notification->setSender($user);
                    $notification->setReceiver($post->getAuthor());
                    $notification->setType(['like']);
                    $notification->setEntityId($post->getId());
                    $notification->setIsRead(false);
                    $em->persist($notification);
                }
                $isLiked = true;
                $this->logger->info('Like added for post '.$id.' by user '.$user->getId());
            }

            $em->flush();

            // Requête directe pour éviter les problèmes de cache
            $likeCount = $likeRepo->count(['post' => $post]);

            return new JsonResponse([
                'liked' => $isLiked,
                'count' => $likeCount
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Like error: '.$e->getMessage(), [
                'exception' => $e,
                'post_id' => $id,
                'user_id' => $user ? $user->getId() : null
            ]);

            return new JsonResponse([
                'error' => 'An error occurred',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

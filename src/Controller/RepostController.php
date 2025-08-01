<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Repost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class RepostController extends AbstractController
{
    #[Route('/post/{id}/repost', name: 'post_repost', methods: ['POST'])]
    public function repost(Post $post, EntityManagerInterface $em): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Non authentifié'
                ], 403);
            }

            $existing = $em->getRepository(Repost::class)->findOneBy([
                'user' => $user,
                'post' => $post
            ]);

            if ($existing) {
                $em->remove($existing);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'reposted' => false,
                    'repostsCount' => count($post->getReposts())
                ]);
            }

            $repost = new Repost();
            $repost->setUser($user);
            $repost->setPost($post);
            $repost->setCreatedAt(new \DateTimeImmutable());

            $em->persist($repost);
            $em->flush();

            return $this->json([
                'success' => true,
                'reposted' => true,
                'repostsCount' => count($post->getReposts())
            ]);
        } catch (\Throwable $e) {


            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

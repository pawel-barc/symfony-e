<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Repost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        if ($existingRepost) {
            return $this->json([
                'success' => false,
                'message' => 'Vous avez déjà reposté ce post.'
            ], 400);
        }

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
}

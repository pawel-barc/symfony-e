<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Repost;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    #[Route('/feed', name: 'app_feed')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Récup posts + reposts
        $posts = $em->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);
        $reposts = $em->getRepository(Repost::class)->findBy([], ['createdAt' => 'DESC']);

        $timeline = [];
        foreach ($posts as $post) {
            $timeline[] = [
                'type' => 'post',
                'data' => $post,
                'createdAt' => $post->getCreatedAt()
            ];
        }
        foreach ($reposts as $repost) {
            $timeline[] = [
                'type' => 'repost',
                'data' => $repost->getPost(),
                'createdAt' => $repost->getCreatedAt(),
                'user' => $repost->getUser()
            ];
        }

        // Tri du feed (posts + reposts)
        usort($timeline, fn($a, $b) => $b['createdAt'] <=> $a['createdAt']);

        // Génération des formulaires de commentaires
        $commentForms = [];
        foreach ($timeline as $item) {
            $post = $item['data'];
            $comment = new Comment();
            $comment->setPost($post);
            $commentForms[$post->getId()] = $this->createForm(CommentType::class, $comment)->createView();
        }

        return $this->render('feed/index.html.twig', [
            'timeline' => $timeline,
            'comment_forms' => $commentForms,
        ]);
    }

    #[Route('/feed/reload', name: 'feed_reload')]
    public function reload(EntityManagerInterface $em): Response
    {
        // Récup posts + reposts
        $posts = $em->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);
        $reposts = $em->getRepository(Repost::class)->findBy([], ['createdAt' => 'DESC']);

        $timeline = [];
        foreach ($posts as $post) {
            $timeline[] = [
                'type' => 'post',
                'data' => $post,
                'createdAt' => $post->getCreatedAt()
            ];
        }
        foreach ($reposts as $repost) {
            $timeline[] = [
                'type' => 'repost',
                'data' => $repost->getPost(),
                'createdAt' => $repost->getCreatedAt(),
                'user' => $repost->getUser()
            ];
        }

        // Tri du feed (posts + reposts)
        usort($timeline, fn($a, $b) => $b['createdAt'] <=> $a['createdAt']);

        return $this->render('post/_feed_list.html.twig', [
            'timeline' => $timeline,
        ]);
    }
}

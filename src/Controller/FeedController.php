<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
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
        // Partie commentaires
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setUser($this->getUser());
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('app_feed');
        }

        // Récupération des commentaires et des posts
        $comments = $em->getRepository(Comment::class)->findBy([], ['createdAt' => 'DESC']);
        $posts = $em->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('feed/index.html.twig', [
            'form' => $form->createView(),
            'comments' => $comments,
            'posts' => $posts, // On ajoute les posts au template
        ]);
    }
}

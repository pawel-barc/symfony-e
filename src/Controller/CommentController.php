<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Notification;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comments')]
class CommentController extends AbstractController
{
    #[Route('/post/{postId}', name: 'app_comment_post', methods: ['POST'])]
    public function addPostComment(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, int $postId): Response
    {
        $post = $postRepository->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $comment->setAuthor($user);
            $comment->setPost($post);
            $entityManager->persist($comment);

            // Notification pour l'auteur du post (si ce n'est pas lui qui commente)
            if ( $post->getUser() !== $user) {
                $notification = new Notification();
                $notification->setSender($user);
                $notification->setReceiver($post->getUser());
                $notification->setType(['comment']);
                $notification->setEntityId($post->getId());
                $notification->setIsRead(false);
                $entityManager->persist($notification);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $postId]);
    }

    #[Route('/reply/{parentId}', name: 'app_comment_reply', methods: ['POST'])]
    public function addReplyComment(Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository, int $parentId): Response
    {
        $parentComment = $commentRepository->find($parentId);
        if (!$parentComment) {
            throw $this->createNotFoundException('Parent comment not found');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $comment->setAuthor($user);
            $comment->setPost($parentComment->getPost());
            $comment->setParentComment($parentComment);
            $entityManager->persist($comment);
            
            // Notification pour l'auteur du commentaire parent (si ce n'est pas lui qui répond)
            if ( $parentComment->getAuthor() !== $user) {
                $notification = new Notification();
                $notification->setSender($user);
                $notification->setReceiver($parentComment->getAuthor());
                $notification->setType(['reply']);
                $notification->setEntityId($parentComment->getPost()->getId());
                $notification->setIsRead(false);
                $entityManager->persist($notification);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $parentComment->getPost()->getId()]);
    }
}

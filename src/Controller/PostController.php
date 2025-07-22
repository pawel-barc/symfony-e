<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_post')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Création d'un nouveau post
        $post = new Post();

        // Check si form POST
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content', '');
            if (!empty(trim($content))) {
                $post->setText($content);
                $post->setAuthor($this->getUser());
                $post->setCreatedAt(new \DateTimeImmutable());

                $em->persist($post);
                $em->flush();

                return $this->redirectToRoute('app_post');
            }
        }

        // Récupérer tous les posts ordonnés par date décroissante
        $posts = $em->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/{id}', name: 'post_show')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}

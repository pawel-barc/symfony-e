<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_post', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content', '');
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $request->files->get('media');

            // Validation minimale
            if (empty(trim($content)) && !$uploadedFile) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['success' => false, 'error' => 'Vous devez écrire du texte ou ajouter un média.'], 400);
                }
                $this->addFlash('error', 'Vous devez écrire du texte ou ajouter un média.');
                return $this->redirectToRoute('app_post');
            }

            $post = new Post();
            $post->setText($content);
            $post->setAuthor($this->getUser());
            $post->setCreatedAt(new \DateTimeImmutable());

            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posted_medias';

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = strtolower($uploadedFile->guessExtension());

                if (!$extension) {
                    $extension = strtolower(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION));
                }
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $uploadedFile->move($uploadDir, $newFilename);
                    $relativePath = '/uploads/posted_medias/' . $newFilename;

                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $post->setImage($relativePath);
                    } elseif (in_array($extension, ['mp4', 'webm', 'ogg'])) {
                        $post->setVideo($relativePath);
                    } else {
                        if ($request->isXmlHttpRequest()) {
                            return new JsonResponse(['success' => false, 'error' => 'Type de fichier non supporté : .' . $extension], 400);
                        }
                        $this->addFlash('error', 'Type de fichier non supporté : .' . $extension);
                        return $this->redirectToRoute('app_post');
                    }
                } catch (\Exception $e) {
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier : ' . $e->getMessage()], 500);
                    }
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement du fichier : ' . $e->getMessage());
                    return $this->redirectToRoute('app_post');
                }
            }

            try {
                $em->persist($post);
                $em->flush();
            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['success' => false, 'error' => 'Erreur lors de la création du post : ' . $e->getMessage()], 500);
                }
                $this->addFlash('error', 'Erreur lors de la création du post : ' . $e->getMessage());
                return $this->redirectToRoute('app_post');
            }

            if ($request->isXmlHttpRequest()) {
                // Retourne les infos du post pour que le JS puisse l’afficher direct
                return new JsonResponse([
                    'success' => true,
                    'post' => [
                        'id' => $post->getId(),
                        'text' => $post->getText(),
                        'image' => $post->getImage(),
                        'video' => $post->getVideo(),
                        'author' => $post->getAuthor()->getUsername(),
                        'createdAt' => $post->getCreatedAt()->format('d/m/Y H:i'),
                    ],
                ]);
            }

            $this->addFlash('success', 'Post créé avec succès.');
            return $this->redirectToRoute('app_post');
        }

        $posts = $em->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('post/sidebar.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }


}

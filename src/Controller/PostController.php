<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\PostLike;
use App\Entity\Repost;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\User;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_post', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content', '');
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $request->files->get('media');

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
                $extension = strtolower($uploadedFile->guessExtension() ?: pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION));
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
                return $this->render('post/_post_item.html.twig', [
                    'post' => $post,
                    'profile_user' => $post->getAuthor(),
                    'type' => 'post'
                ]);
            }

            $this->addFlash('success', 'Post créé avec succès.');
            return $this->redirectToRoute('app_post');
        }

        // ---- Feed : posts + reposts ----
        $posts = $em->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);
        $reposts = $em->getRepository(Repost::class)->findBy([], ['createdAt' => 'DESC']);

        $timeline = [];
        foreach ($posts as $post) {
            $timeline[] = [
                'type' => 'post',
                'data' => $post,
                'user' => $post->getAuthor(),
                'createdAt' => $post->getCreatedAt()
            ];
        }
        foreach ($reposts as $repost) {
            $timeline[] = [
                'type' => 'repost',
                'data' => $repost->getPost(),
                'user' => $repost->getUser(),
                'createdAt' => $repost->getCreatedAt()
            ];
        }
        usort($timeline, fn($a, $b) => $b['createdAt'] <=> $a['createdAt']);

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

    #[Route('/profile/{username}', name: 'app_user_profile')]
    public function profile(string $username, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $posts = $em->getRepository(Post::class)->findBy(['author' => $user], ['createdAt' => 'DESC']);
        $reposts = $em->getRepository(Repost::class)->findBy(['user' => $user], ['createdAt' => 'DESC']);

        $timeline = [];
        foreach ($posts as $post) {
            $timeline[] = [
                'type' => 'post',
                'data' => $post,
                'user' => $post->getAuthor(),
                'createdAt' => $post->getCreatedAt()
            ];
        }
        foreach ($reposts as $repost) {
            $timeline[] = [
                'type' => 'repost',
                'data' => $repost->getPost(),
                'user' => $repost->getUser(),
                'createdAt' => $repost->getCreatedAt()
            ];
        }
        usort($timeline, fn($a, $b) => $b['createdAt'] <=> $a['createdAt']);

        return $this->render('public.html.twig', [
            'user' => $user,
            'timeline' => $timeline,
        ]);
    }

    #[Route('/post/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $commentForm = $this->createForm(CommentType::class, new Comment());
        $commentForms = [$post->getId() => $commentForm->createView()];

        return $this->render('post/_post_details.html.twig', [
            'post' => $post,
            'comment_forms' => $commentForms,
        ]);
    }

    #[Route('/post/{id}/comment', name: 'post_add_comment', methods: ['POST'])]
    public function addComment(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $em->persist($comment);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'comment' => [
                        'id' => $comment->getId(),
                        'text' => $comment->getText(),
                        'author' => $comment->getAuthor()->getUsername(),
                        'createdAt' => $comment->getCreatedAt()->format('d/m/Y H:i'),
                    ],
                    'commentsCount' => $post->getComments()->count()
                ]);
            }

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'errors' => (string) $form->getErrors(true, false)], 400);
        }

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    public function likePost(Post $post, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $alreadyLiked = $post->isLikedByUser($user);

        if ($alreadyLiked) {
            foreach ($post->getPostLikes() as $like) {
                if ($like->getAuthor() === $user) {
                    $em->remove($like);
                }
            }
        } else {
            $like = new PostLike();
            $like->setPost($post);
            $like->setAuthor($user);
            $like->setCreatedAt(new \DateTimeImmutable());
            $em->persist($like);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'liked' => !$alreadyLiked,
            'count' => $post->getPostLikes()->count()
        ]);
    }

    #[Route('/post/{id}/json', name: 'post_json', methods: ['GET'])]
    public function getPostJson(Post $post): Response
    {
        return $this->json([
            'id' => $post->getId(),
            'text' => $post->getText(),
            'image' => $post->getImage(),
            'video' => $post->getVideo(),
            'author' => $post->getAuthor()->getUsername(),
            'createdAt' => $post->getCreatedAt()->format('d/m/Y H:i'),
            'commentsCount' => $post->getComments()->count(),
        ]);
    }
}

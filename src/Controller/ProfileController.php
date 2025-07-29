<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Repost;
use App\Form\ProfileFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class ProfileController extends AbstractController
{
    #[Route('/profile/me/edit', name: 'app_profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder au profil');
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('profile_images_directory'), $newFilename);
                    $user->setProfileImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Une erreur s'est produite lors de l'ajout de l'image.");
                }
            }

            $em->flush();
            $this->addFlash('success', 'Le profil a été mis à jour.');

            return $this->redirectToRoute('app_profile_edit');
        }

        return $this->render('profile/edit.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/user/{username}', name: 'app_user_profile')]
    public function showUserProfile(UserRepository $userRepository, EntityManagerInterface $em, string $username): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        // Récupérer les posts de l’utilisateur
        $posts = $em->getRepository(Post::class)->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );

        // Récupérer les reposts de l’utilisateur
        $reposts = $em->getRepository(Repost::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        // Fusionner et trier par date
        $timeline = [];

        foreach ($posts as $post) {
            $timeline[] = [
                'type' => 'post',
                'createdAt' => $post->getCreatedAt(),
                'data' => $post,
            ];
        }

        foreach ($reposts as $repost) {
            $timeline[] = [
                'type' => 'repost',
                'createdAt' => $repost->getCreatedAt(),
                'data' => $repost->getPost(),
            ];
        }

        usort($timeline, fn($a, $b) => $b['createdAt'] <=> $a['createdAt']);

        return $this->render('profile/index.html.twig', [
            'user'     => $user,
            'timeline' => $timeline,
        ]);
    }

    #[Route('/user/{username}/reposts', name: 'app_user_reposts')]
    public function showUserReposts(UserRepository $userRepository, EntityManagerInterface $em, string $username): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        // Récupérer seulement les reposts
        $reposts = $em->getRepository(Repost::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/reposts.html.twig', [
            'user'     => $user,
            'reposts'  => $reposts,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit_alt')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès ✅');

            return $this->redirectToRoute('app_user_profile', [
                'username' => $user->getUsername(),
            ]);
        }

        return $this->render('profile/edit.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
}

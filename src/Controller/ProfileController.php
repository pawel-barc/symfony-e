<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
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
                    $this->addFlash('danger', "Une erreur s'est produit lors de l'ajout d'image.");
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
}

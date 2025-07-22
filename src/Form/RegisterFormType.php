<?php

namespace App\Form;
// Ensemble des composants nécessaires pour la construction du Formulaire
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

// RegisterTypeForm hérite des prioprietés d'AbstractType afin de gérer le formulaire
class RegisterFormType extends AbstractType
{
    // Méthode buildForm permet de créer le formulaire d'inscription avec des options de validation
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
            ])
            ->add('email', EmailType::class)
            // Champ pour le mot de passe avec confirmation ( deux champs identiques requis)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'required' => true,
                'first_options' => [ 'label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
                // 'mapped' => false

            ])
            ->add('profileImage', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Photo de profil',
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier image valide (JPEG, PNG, WEBP, GIF)',
                    ])
                    ],
            ])
            ->add('bio', TextType::class, [
                'required' => false,
                'label' => 'Bio'
            ])
        ;
    }

    // Configure les options par défaut du formulaire
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

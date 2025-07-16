<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ID
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Prénom
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(
        min: 2,
        minMessage: "Le prénom doit contenir au moins 2 caractères."
    )]
    private ?string $firstname = null;

    // Nom de famille
    #[ORM\Column(length: 50)]
    #[Assert\Length(
        min: 2,
        minMessage: "Le nom doit contenir au moins deux caractères."
    )]
    private ?string $lastname = null;

    // Nom d'utilisateur
    #[ORM\Column(length: 50, unique: true, )]
    #[Assert\Length(
        min: 2,
        minMessage: "Le nom doit contenir au moins deux caractères."
    )]
    private ?string $username = null;

    // Adresse e-mail
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email n'est pas valide.")]
    private ?string $email = null;

    // Mot de passe
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/",
        message: "Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial"
    )]
    private ?string $password = null;

    // Image du profil
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profileImage = null;

    // Bio
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    // Date de création
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    // Rôle
    #[ORM\Column]
    private array $roles = [];

    // Retourne l'identifiant unique de l'utilisateur
    public function getId(): ?int
    {
        return $this->id;
    }

    // Prénom
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    // Nom de famille
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    // Nom d'utilisateur
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    // Adresse e-mail
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Mot de passe
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Image du profile
    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): static
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    // Bio
    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio) : static
    {
        $this->bio = $bio;
        return $this;
    }

    // Role
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] ='ROLE_USER';
        // Chaque utilisateur a au minimum le rôle ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    // Date de création
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt) : static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function eraseCredentials(): void
    {
        //Efface les données sensibles
    }
}

<?php

namespace App\Entity;

// Import des classes nécessaires pour gérer les collections (relations ManyToMany)
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()] // Annotation indiquant que cette classe est une entité Doctrine
class Hashtag
{
    // ID unique du hashtag (clé primaire en base)
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    // Nom du hashtag (ex: #cc) qui doit être unique dans la base
    #[ORM\Column(type: "string", length: 255, unique: true)]
    private ?string $name = null;

    // Relation ManyToMany avec l'entité Post
    // Un hashtag peut être lié à plusieurs posts et inversement
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: "hashtags")]
    private Collection $posts;

    public function __construct()
    {
        // Initialisation de la collection des posts pour éviter les erreurs
        $this->posts = new ArrayCollection();
    }

    // --- GETTERS & SETTERS ---

    // Retourne l'ID du hashtag
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne le nom du hashtag
    public function getName(): ?string
    {
        return $this->name;
    }

    // Définit le nom du hashtag
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Retourne la liste des posts qui contiennent ce hashtag
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    // Ajoute un post à la collection et synchronise la relation du côté Post
    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->addHashtag($this); // Maintient la cohérence des deux côtés de la relation
        }
        return $this;
    }

    // Retire un post de la collection et synchronise du côté Post
    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            $post->removeHashtag($this);
        }
        return $this;
    }

    // Permet d'afficher directement le nom du hashtag quand on fait un echo de l'objet
    public function __toString(): string
    {
        return $this->name ?? '';
    }
}

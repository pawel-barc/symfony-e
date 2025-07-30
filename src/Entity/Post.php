<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $video = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, orphanRemoval: true, cascade: ['remove'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: PostLike::class, orphanRemoval: true, cascade: ['remove'])]
    private Collection $postLikes;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Repost::class, orphanRemoval: true, cascade: ['remove'])]
    private Collection $reposts;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->postLikes = new ArrayCollection();
        $this->reposts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }
        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, PostLike>
     */
    public function getPostLikes(): Collection
    {
        return $this->postLikes;
    }

    public function addPostLike(PostLike $like): static
    {
        if (!$this->postLikes->contains($like)) {
            $this->postLikes->add($like);
            $like->setPost($this);
        }
        return $this;
    }

    public function removePostLike(PostLike $like): static
    {
        if ($this->postLikes->removeElement($like)) {
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Repost>
     */
    public function getReposts(): Collection
    {
        return $this->reposts;
    }

    public function addRepost(Repost $repost): static
    {
        if (!$this->reposts->contains($repost)) {
            $this->reposts->add($repost);
            $repost->setPost($this);
        }
        return $this;
    }

    public function removeRepost(Repost $repost): static
    {
        if ($this->reposts->removeElement($repost)) {
            if ($repost->getPost() === $this) {
                $repost->setPost(null);
            }
        }
        return $this;
    }

    public function isRepostedByUser(?User $user): bool
    {
        if (!$user) return false;
        foreach ($this->reposts as $repost) {
            if ($repost->getUser() === $user) {
                return true;
            }
        }
        return false;
    }
}

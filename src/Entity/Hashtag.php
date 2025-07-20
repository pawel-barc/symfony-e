<?php

namespace App\Entity;

use App\Repository\HashtagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HashtagRepository::class)]
class Hashtag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, PostHashtag>
     */
    #[ORM\OneToMany(targetEntity: PostHashtag::class, mappedBy: 'hashtag', orphanRemoval: true)]
    private Collection $postHashtags;

    /**
     * @var Collection<int, UserHashtagLike>
     */
    #[ORM\OneToMany(targetEntity: UserHashtagLike::class, mappedBy: 'hashtag', orphanRemoval: true)]
    private Collection $userHashtagLikes;

    public function __construct()
    {
        $this->postHashtags = new ArrayCollection();
        $this->userHashtagLikes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, PostHashtag>
     */
    public function getPostHashtags(): Collection
    {
        return $this->postHashtags;
    }

    public function addPostHashtag(PostHashtag $postHashtag): static
    {
        if (!$this->postHashtags->contains($postHashtag)) {
            $this->postHashtags->add($postHashtag);
            $postHashtag->setHashtag($this);
        }

        return $this;
    }

    public function removePostHashtag(PostHashtag $postHashtag): static
    {
        if ($this->postHashtags->removeElement($postHashtag)) {
            // set the owning side to null (unless already changed)
            if ($postHashtag->getHashtag() === $this) {
                $postHashtag->setHashtag(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserHashtagLike>
     */
    public function getUserHashtagLikes(): Collection
    {
        return $this->userHashtagLikes;
    }

    public function addUserHashtagLike(UserHashtagLike $userHashtagLike): static
    {
        if (!$this->userHashtagLikes->contains($userHashtagLike)) {
            $this->userHashtagLikes->add($userHashtagLike);
            $userHashtagLike->setHashtag($this);
        }

        return $this;
    }

    public function removeUserHashtagLike(UserHashtagLike $userHashtagLike): static
    {
        if ($this->userHashtagLikes->removeElement($userHashtagLike)) {
            // set the owning side to null (unless already changed)
            if ($userHashtagLike->getHashtag() === $this) {
                $userHashtagLike->setHashtag(null);
            }
        }

        return $this;
    }
}

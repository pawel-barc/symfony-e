<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author', cascade: ['persist', 'remove'])]
    private Collection $posts;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author')]
    private Collection $comments;

    /**
     * @var Collection<int, PostLike>
     */
    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'author')]
    private Collection $postLikes;

    /**
     * @var Collection<int, CommentLike>
     */
    #[ORM\OneToMany(targetEntity: CommentLike::class, mappedBy: 'author')]
    private Collection $commentLikes;

    /**
     * @var Collection<int, Repost>
     */
    #[ORM\OneToMany(targetEntity: Repost::class, mappedBy: 'author')]
    private Collection $reposts;

    /**
     * @var Collection<int, Follow>
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'follower')]
    private Collection $follows;

    /**
     * @var Collection<int, Follow>
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'followed')]
    private Collection $followers;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'receiver')]
    private Collection $receivedNotifications;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'sender')]
    private Collection $sentNotifications;

    /**
     * @var Collection<int, UserHashtagLike>
     */
    #[ORM\OneToMany(targetEntity: UserHashtagLike::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $userHashtagLikes;

    /**
     * @var Collection<int, PrivateMessage>
     */
    #[ORM\OneToMany(targetEntity: PrivateMessage::class, mappedBy: 'author')]
    private Collection $privateMessages;

    /**
     * @var Collection<int, ChatUser>
     */
    #[ORM\OneToMany(targetEntity: ChatUser::class, mappedBy: 'author')]
    private Collection $chatUsers;
    


    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->postLikes = new ArrayCollection();
        $this->commentLikes = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->reposts = new ArrayCollection();
        $this->follows = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->receivedNotifications = new ArrayCollection();
        $this->sentNotifications = new ArrayCollection();
        $this->userHashtagLikes = new ArrayCollection();
        $this->privateMessages = new ArrayCollection();
        $this->chatUsers = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }
        return $this;
    }
    
    public function removePost(Post $post): static
    {
        if($this->posts->removeElement($post)) {
            if($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }
        return $this;
    }

    public function addPostLike(PostLike $postLike): static
    {
        if (!$this->postLikes->contains($postLike)) {
            $this->postLikes->add($postLike);
            $postLike->setAuthor($this);
        }

        return $this;
    }

    public function removePostLike(PostLike $postLike): static
    {
        if ($this->postLikes->removeElement($postLike)) {
            // set the owning side to null (unless already changed)
            if ($postLike->getAuthor() === $this) {
                $postLike->setAuthor(null);
            }
        }

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
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
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

    /**
     * @return Collection<int, CommentLike>
     */
    public function getCommentLikes(): Collection
    {
        return $this->commentLikes;
    }

    public function addCommentLike(CommentLike $commentLike): static
    {
        if (!$this->commentLikes->contains($commentLike)) {
            $this->commentLikes->add($commentLike);
            $commentLike->setAuthor($this);
        }

        return $this;
    }

    public function removeCommentLike(CommentLike $commentLike): static
    {
        if ($this->commentLikes->removeElement($commentLike)) {
            // set the owning side to null (unless already changed)
            if ($commentLike->getAuthor() === $this) {
                $commentLike->setAuthor(null);
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
            $repost->setAuthor($this);
        }

        return $this;
    }

    public function removeRepost(Repost $repost): static
    {
        if ($this->reposts->removeElement($repost)) {
            // set the owning side to null (unless already changed)
            if ($repost->getAuthor() === $this) {
                $repost->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function addFollow(Follow $follow): static
    {
        if (!$this->follows->contains($follow)) {
            $this->follows->add($follow);
            $follow->setFollower($this);
        }

        return $this;
    }

    public function removeFollow(Follow $follow): static
    {
        if ($this->follows->removeElement($follow)) {
            // set the owning side to null (unless already changed)
            if ($follow->getFollower() === $this) {
                $follow->setFollower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Follow $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->setFollowed($this);
        }

        return $this;
    }

    public function removeFollower(Follow $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            // set the owning side to null (unless already changed)
            if ($follower->getFollowed() === $this) {
                $follower->setFollowed(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getReceivedNotifications(): Collection
    {
        return $this->receivedNotifications;
    }

    public function addReceivedNotification(Notification $receivedNotification): static
    {
        if (!$this->receivedNotifications->contains($receivedNotification)) {
            $this->receivedNotifications->add($receivedNotification);
            $receivedNotification->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedNotification(Notification $receivedNotification): static
    {
        if ($this->receivedNotifications->removeElement($receivedNotification)) {
            // set the owning side to null (unless already changed)
            if ($receivedNotification->getReceiver() === $this) {
                $receivedNotification->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getSentNotifications(): Collection
    {
        return $this->sentNotifications;
    }

    public function addSentNotification(Notification $sentNotification): static
    {
        if (!$this->sentNotifications->contains($sentNotification)) {
            $this->sentNotifications->add($sentNotification);
            $sentNotification->setSender($this);
        }

        return $this;
    }

    public function removeSentNotification(Notification $sentNotification): static
    {
        if ($this->sentNotifications->removeElement($sentNotification)) {
            // set the owning side to null (unless already changed)
            if ($sentNotification->getSender() === $this) {
                $sentNotification->setSender(null);
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
            $userHashtagLike->setAuthor($this);
        }

        return $this;
    }

    public function removeUserHashtagLike(UserHashtagLike $userHashtagLike): static
    {
        if ($this->userHashtagLikes->removeElement($userHashtagLike)) {
            // set the owning side to null (unless already changed)
            if ($userHashtagLike->getAuthor() === $this) {
                $userHashtagLike->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrivateMessage>
     */
    public function getPrivateMessages(): Collection
    {
        return $this->privateMessages;
    }

    public function addPrivateMessage(PrivateMessage $privateMessage): static
    {
        if (!$this->privateMessages->contains($privateMessage)) {
            $this->privateMessages->add($privateMessage);
            $privateMessage->setAuthor($this);
        }

        return $this;
    }

    public function removePrivateMessage(PrivateMessage $privateMessage): static
    {
        if ($this->privateMessages->removeElement($privateMessage)) {
            // set the owning side to null (unless already changed)
            if ($privateMessage->getAuthor() === $this) {
                $privateMessage->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChatUser>
     */
    public function getChatUsers(): Collection
    {
        return $this->chatUsers;
    }

    public function addChatUser(ChatUser $chatUser): static
    {
        if (!$this->chatUsers->contains($chatUser)) {
            $this->chatUsers->add($chatUser);
            $chatUser->setAuthor($this);
        }

        return $this;
    }

    public function removeChatUser(ChatUser $chatUser): static
    {
        if ($this->chatUsers->removeElement($chatUser)) {
            // set the owning side to null (unless already changed)
            if ($chatUser->getAuthor() === $this) {
                $chatUser->setAuthor(null);
            }
        }

        return $this;
    }

}

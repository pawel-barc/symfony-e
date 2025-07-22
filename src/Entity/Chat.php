<?php

namespace App\Entity;

use App\Repository\ChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: ChatRepository::class)]
#[ApiResource]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, PrivateMessage>
     */
    #[ORM\OneToMany(targetEntity: PrivateMessage::class, mappedBy: 'chat')]
    private Collection $privateMessages;

    /**
     * @var Collection<int, ChatUser>
     */
    #[ORM\OneToMany(targetEntity: ChatUser::class, mappedBy: 'chat')]
    private Collection $chatUsers;

    public function __construct()
    {
        $this->privateMessages = new ArrayCollection();
        $this->chatUsers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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
            $privateMessage->setChat($this);
        }

        return $this;
    }

    public function removePrivateMessage(PrivateMessage $privateMessage): static
    {
        if ($this->privateMessages->removeElement($privateMessage)) {
            // set the owning side to null (unless already changed)
            if ($privateMessage->getChat() === $this) {
                $privateMessage->setChat(null);
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
            $chatUser->setChat($this);
        }

        return $this;
    }

    public function removeChatUser(ChatUser $chatUser): static
    {
        if ($this->chatUsers->removeElement($chatUser)) {
            // set the owning side to null (unless already changed)
            if ($chatUser->getChat() === $this) {
                $chatUser->setChat(null);
            }
        }

        return $this;
    }
}

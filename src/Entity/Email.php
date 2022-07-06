<?php

namespace App\Entity;

use App\Repository\EmailRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmailRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Email implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int | null $id = null;

    #[ORM\Column(type: 'string', length: 45, unique: true)]
    #[Assert\Email]
    private string | null $email;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable | null $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private \DateTimeImmutable | null $lastVerifiedAt = null;

    #[ORM\OneToMany(mappedBy: 'email', targetEntity: EmailVerification::class, cascade: ["all"], orphanRemoval: true)]
    private Collection $emailVerifications;

    public function __construct()
    {
        $this->emailVerifications = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastVerifiedAt(): ?DateTimeImmutable
    {
        return $this->lastVerifiedAt;
    }

    public function setLastVerifiedAt(?DateTimeImmutable $lastVerifiedAt): self
    {
        $this->lastVerifiedAt = $lastVerifiedAt;

        return $this;
    }

    /**
     * @return Collection<int, EmailVerification>
     */
    public function getEmailVerifications(): Collection
    {
        return $this->emailVerifications;
    }

    public function addEmailVerification(EmailVerification $emailVerification): self
    {
        if (!$this->emailVerifications->contains($emailVerification)) {
            $this->emailVerifications[] = $emailVerification;
            $emailVerification->setEmail($this);
        }

        return $this;
    }

    public function removeEmailVerification(EmailVerification $emailVerification): self
    {
        if ($this->emailVerifications->removeElement($emailVerification)) {
            // set the owning side to null (unless already changed)
            if ($emailVerification->getEmail() === $this) {
                $emailVerification->setEmail(null);
            }
        }

        return $this;
    }

    public function getLastVerification(): ?EmailVerification
    {
        return $this->emailVerifications->last() ?: null;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'createdAt' => $this->getCreatedAt()?->format('Y-m-d H:i:s'),
            'verification' => $this->getLastVerification(),
        ];
    }
}

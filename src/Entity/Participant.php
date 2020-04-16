<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipantRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Participant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wod", inversedBy="participants")
     */
    private $wod;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastSeenAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="participants")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track", mappedBy="participant")
     */
    private $tracks;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Image", inversedBy="participants")
     */
    private $image;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWod(): ?Wod
    {
        return $this->wod;
    }

    public function setWod(?Wod $wod): self
    {
        $this->wod = $wod;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(\DateTimeInterface $lastSeenAt): self
    {
        $this->lastSeenAt = $lastSeenAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Track[]
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addTrack(Track $track): self
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks[] = $track;
            $track->setParticipant($this);
        }

        return $this;
    }

    public function removeTrack(Track $track): self
    {
        if ($this->tracks->contains($track)) {
            $this->tracks->removeElement($track);
            // set the owning side to null (unless already changed)
            if ($track->getParticipant() === $this) {
                $track->setParticipant(null);
            }
        }

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }
}

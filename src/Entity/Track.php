<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrackRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Track
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $wodDescription;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wodRating;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wod", inversedBy="tracks")
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $score;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $feeling;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tracks")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rxOrScaled;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWodDescription(): ?string
    {
        return $this->wodDescription;
    }

    public function setWodDescription(string $wodDescription): self
    {
        $this->wodDescription = $wodDescription;

        return $this;
    }

    public function getWodRating(): ?int
    {
        return $this->wodRating;
    }

    public function setWodRating(?int $wodRating): self
    {
        $this->wodRating = $wodRating;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

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

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getFeeling(): ?int
    {
        return $this->feeling;
    }

    public function setFeeling(?int $feeling): self
    {
        $this->feeling = $feeling;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRxOrScaled(): ?string
    {
        return $this->rxOrScaled;
    }

    public function setRxOrScaled(string $rxOrScaled): self
    {
        $this->rxOrScaled = $rxOrScaled;

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

    public function getScoreText()
    {
        switch ($this->getType()) {
        case '':
            $text = null;
            break;
        case 'time':
            $text = gmdate('H:i:s', $this->getScore());
            break;

        case 'reps':
            $text = sprintf('%s reps', $this->getScore());
            break;

        case 'load':
            $text = sprintf('%s kilo', $this->getScore());
            break;

        case 'calories':
            $text = sprintf('%s cal', $this->getScore());
            break;

        case 'points':
            $text = sprintf('%s points', $this->getScore());
            break;

        case 'meters':
            $text = sprintf('%s meters', $this->getScore());
            break;
        }

        return $text;
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Participant", mappedBy="user")
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track", mappedBy="user")
     */
    private $tracks;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Wod", mappedBy="user")
     */
    private $wods;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="users")
     */
    private $organization;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization", mappedBy="user")
     */
    private $organizations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserOrganization", mappedBy="user")
     */
    private $userOrganizations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="user")
     */
    private $images;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->tracks = new ArrayCollection();
        $this->wods = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->userOrganizations = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setUser($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
            // set the owning side to null (unless already changed)
            if ($participant->getUser() === $this) {
                $participant->setUser(null);
            }
        }

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
            $track->setUser($this);
        }

        return $this;
    }

    public function removeTrack(Track $track): self
    {
        if ($this->tracks->contains($track)) {
            $this->tracks->removeElement($track);
            // set the owning side to null (unless already changed)
            if ($track->getUser() === $this) {
                $track->setUser(null);
            }
        }

        return $this;
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

    /**
     * @return Collection|Wod[]
     */
    public function getWods(): Collection
    {
        return $this->wods;
    }

    public function addWod(Wod $wod): self
    {
        if (!$this->wods->contains($wod)) {
            $this->wods[] = $wod;
            $wod->setUser($this);
        }

        return $this;
    }

    public function removeWod(Wod $wod): self
    {
        if ($this->wods->contains($wod)) {
            $this->wods->removeElement($wod);
            // set the owning side to null (unless already changed)
            if ($wod->getUser() === $this) {
                $wod->setUser(null);
            }
        }

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return Collection|Organization[]
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): self
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations[] = $organization;
            $organization->setUser($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
            // set the owning side to null (unless already changed)
            if ($organization->getUser() === $this) {
                $organization->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserOrganization[]
     */
    public function getUserOrganizations(): Collection
    {
        return $this->userOrganizations;
    }

    public function addUserOrganization(UserOrganization $userOrganization): self
    {
        if (!$this->userOrganizations->contains($userOrganization)) {
            $this->userOrganizations[] = $userOrganization;
            $userOrganization->setUser($this);
        }

        return $this;
    }

    public function removeUserOrganization(UserOrganization $userOrganization): self
    {
        if ($this->userOrganizations->contains($userOrganization)) {
            $this->userOrganizations->removeElement($userOrganization);
            // set the owning side to null (unless already changed)
            if ($userOrganization->getUser() === $this) {
                $userOrganization->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setUser($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getUser() === $this) {
                $image->setUser(null);
            }
        }

        return $this;
    }
}

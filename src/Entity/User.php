<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: '"user"')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(mercure: true)]
#[ORM\HasLifecycleCallbacks]
class User
{
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Addiction::class, orphanRemoval: true)]
    private Collection $addictions;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->addictions = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, Addiction>
     */
    public function getAddictions(): Collection
    {
        return $this->addictions;
    }

    public function addAddiction(Addiction $addiction): static
    {
        if (!$this->addictions->contains($addiction)) {
            $this->addictions->add($addiction);
            $addiction->setUser($this);
        }

        return $this;
    }

    public function removeAddiction(Addiction $addiction): static
    {
        if ($this->addictions->removeElement($addiction)) {
            if ($addiction->getUser() === $this) {
                $addiction->setUser(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\UserController;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: '"user"')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiFilter(
    filterClass: SearchFilter::class,
    properties: ['type' => 'ipartial', 'status' => 'iexact', 'user.id' => 'exact']
)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            normalizationContext: ['groups' => ['user:item:read', 'addiction:item:read']]
        ),
        new Post(
            uriTemplate: '/api/register',
            controller: UserController::class,
            name: 'register'
        ),
        new Patch(),
        new Delete(),
    ],
    mercure: true
)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:item:read'])]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(length: 180, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:item:read'])]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Addiction::class, orphanRemoval: true)]
    #[Groups(['user:item:read'])]
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

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }
}

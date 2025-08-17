<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AddictionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AddictionRepository::class)]
#[ApiResource(mercure: true)]
#[ORM\HasLifecycleCallbacks]
class Addiction
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $totalAmount = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'addictions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Trigger>
     */
    #[ORM\ManyToMany(targetEntity: Trigger::class, mappedBy: 'addictions')]
    private Collection $triggers;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->triggers = new ArrayCollection();
    }

    public function getId(): ?string
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Trigger>
     */
    public function getTriggers(): Collection
    {
        return $this->triggers;
    }

    public function addTrigger(Trigger $trigger): static
    {
        if (!$this->triggers->contains($trigger)) {
            $this->triggers->add($trigger);
            $trigger->addAddiction($this);
        }

        return $this;
    }

    public function removeTrigger(Trigger $trigger): static
    {
        if ($this->triggers->removeElement($trigger)) {
            $trigger->removeAddiction($this);
        }

        return $this;
    }
}

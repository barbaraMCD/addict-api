<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\ConsumptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsumptionRepository::class)]
#[ApiResource(mercure: true)]
#[ORM\HasLifecycleCallbacks]
class Consumption
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column]
    private int $quantity = 0;

    #[ORM\Column(length: 255)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'consumptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Addiction $addiction = null;

    #[ORM\ManyToMany(targetEntity: Trigger::class, mappedBy: 'consumptions')]
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAddiction(): ?Addiction
    {
        return $this->addiction;
    }

    public function setAddiction(?Addiction $addiction): static
    {
        $this->addiction = $addiction;

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
            $trigger->addConsumption($this);
        }

        return $this;
    }

    public function removeTrigger(Trigger $trigger): static
    {
        if ($this->triggers->removeElement($trigger)) {
            $trigger->removeConsumption($this);
        }

        return $this;
    }
}

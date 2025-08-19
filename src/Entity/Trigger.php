<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: '"trigger"')]
#[ORM\Entity]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class Trigger
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    /**
     * @var Collection<int, Consumption>
     */
    #[ORM\ManyToMany(targetEntity: Consumption::class, inversedBy: 'triggers')]
    private Collection $consumptions;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->consumptions = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Consumption>
     */
    public function getConsumptions(): Collection
    {
        return $this->consumptions;
    }

    public function addConsumption(Consumption $consumption): static
    {
        if (!$this->consumptions->contains($consumption)) {
            $this->consumptions->add($consumption);
        }

        return $this;
    }
    public function removeConsumption(Consumption $consumption): static
    {
        $this->consumptions->removeElement($consumption);

        return $this;
    }
}

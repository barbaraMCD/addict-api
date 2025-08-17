<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
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
class Trigger
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    /**
     * @var Collection<int, Addiction>
     */
    #[ORM\ManyToMany(targetEntity: Addiction::class, inversedBy: 'triggers')]
    #[ORM\JoinTable(name: 'trigger_addictions')]
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
            $addiction->addTrigger($this);
        }

        return $this;
    }

    public function removeAddiction(Addiction $addiction): static
    {
        if ($this->addictions->removeElement($addiction)) {
            $addiction->removeTrigger($this);
        }

        return $this;
    }
}

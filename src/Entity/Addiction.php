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
use App\Entity\Trait\TimestampableTrait;
use App\Enum\Addiction\Status;
use App\Enum\Addiction\AddictionType;
use App\Repository\AddictionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AddictionRepository::class)]
#[ApiFilter(
    filterClass: SearchFilter::class,
    properties: ['type' => 'ipartial', 'status' => 'iexact', 'user.id' => 'exact']
)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            normalizationContext: ['groups' => ['addiction:item:read', 'consumption:item:read', 'trigger:item:read'],
                'enable_max_depth' => true
            ]
        ),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    mercure: true,
    order: ['type' => 'ASC']
)]
#[ORM\HasLifecycleCallbacks]
class Addiction
{
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(length: 100, enumType: AddictionType::class)]
    #[Groups(['addiction:item:read', 'addiction:consumption:read'])]
    private ?AddictionType $type = null;

    #[ORM\Column(length: 100, enumType: Status::class, options: ['default' => Status::ACTIVE])]
    #[Groups(['addiction:item:read'])]
    private Status $status = Status::ACTIVE;

    #[ORM\Column(length: 10)]
    #[Groups(['addiction:item:read'])]
    private float $totalAmount = 0;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'addictions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'addiction', targetEntity: Consumption::class, orphanRemoval: true)]
    #[Groups(['addiction:item:read'])]
    #[MaxDepth(1)]
    private Collection $consumptions;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->totalAmount = 0.0;
        $this->consumptions = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getType(): ?AddictionType
    {
        return $this->type;
    }

    public function setType(AddictionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $amount): static
    {
        $this->totalAmount += $amount;
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
     * @return Collection<int, Addiction>
     */
    public function getConsumptions(): Collection
    {
        return $this->consumptions;
    }

    public function addConsumption(Consumption $consumption): static
    {
        if (!$this->consumptions->contains($consumption)) {
            $this->consumptions->add($consumption);
            $consumption->setAddiction($this);
        }
        return $this;
    }

    public function removeConsumption(Consumption $consumption): static
    {
        if ($this->consumptions->removeElement($consumption)) {
            if ($consumption->getAddiction() === $this) {
                $consumption->setAddiction(null);
            }
        }
        return $this;
    }
}

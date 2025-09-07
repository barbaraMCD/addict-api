<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\Subscription\PlanType;
use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put()
    ],
)]
class Subscription
{
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(length: 255)]
    private ?string $stripeCustomerId = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeImmutable $currentPeriodStart = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTimeImmutable $currentPeriodEnd = null;

    #[ORM\Column(length: 50, enumType: PlanType::class)]
    private ?PlanType $planType = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function isActive(): bool
    {
        if (!$this->currentPeriodStart || !$this->currentPeriodEnd) {
            return false;
        }

        $now = new \DateTimeImmutable();
        return $now >= $this->currentPeriodStart && $now <= $this->currentPeriodEnd;
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(string $stripeSubscriptionId): static
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
        return $this;
    }

    public function getStripeCustomerId(): ?string
    {
        return $this->stripeCustomerId;
    }

    public function setStripeCustomerId(string $stripeCustomerId): static
    {
        $this->stripeCustomerId = $stripeCustomerId;
        return $this;
    }

    public function getCurrentPeriodStart(): ?\DateTimeImmutable
    {
        return $this->currentPeriodStart;
    }

    public function setCurrentPeriodStart(\DateTimeImmutable $currentPeriodStart): static
    {
        $this->currentPeriodStart = $currentPeriodStart;
        return $this;
    }

    public function getCurrentPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(\DateTimeImmutable $currentPeriodEnd): static
    {
        $this->currentPeriodEnd = $currentPeriodEnd;
        return $this;
    }

    public function getPlanType(): ?PlanType
    {
        return $this->planType;
    }

    public function setPlanType(PlanType $planType): static
    {
        $this->planType = $planType;
        return $this;
    }
}

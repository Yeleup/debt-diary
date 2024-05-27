<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Repository\ExpenseRepository;
use App\State\ExpenseStateProcessor;
use App\Validator\User\IsRoleControl;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
#[ApiResource(
    normalizationContext: ["groups" => ["expense.read"]],
    denormalizationContext: ["groups" => ["expense.write"]],
    order: ['createdAt' => 'DESC'],
    processor: ExpenseStateProcessor::class,
)]
#[ApiResource(
    uriTemplate: '/users/{userId}/expenses',
    operations: [ new GetCollection() ],
    uriVariables: [
        'userId' => new Link(toProperty: 'user', fromClass: User::class),
    ],
    normalizationContext: ["groups" => ["user.expense.read"]],
    denormalizationContext: ["groups" => ["user.expense.write"]],
    order: ['createdAt' => 'DESC'],
)]
#[ApiFilter(DateFilter::class, properties: ["createdAt"])]
#[ApiFilter(OrderFilter::class, properties: ["createdAt"])]
#[ORM\HasLifecycleCallbacks]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: true)]
    #[Groups(groups: ['expense.read', 'user.expense.read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 0)]
    #[Groups(groups: ['expense.read', 'expense.write', 'user.expense.read'])]
    private ?float $amount = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['expense.read', 'user.expense.read'])]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['expense.read', 'expense.write', 'user.expense.read'])]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['expense.read', 'expense.write', 'user.expense.read'])]
    private ?ExpenseType $expenseType = null;

    #[ORM\Column(type: 'datetime', nullable: false, name: 'created_at')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'updated_at')]
    private ?\DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'associatedExpenses')]
    #[Groups(groups: ['expense.read', 'expense.write', 'user.expense.read'])]
    private ?User $associatedUser = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getExpenseType(): ?ExpenseType
    {
        return $this->expenseType;
    }

    public function setExpenseType(?ExpenseType $expenseType): static
    {
        $this->expenseType = $expenseType;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAssociatedUser(): ?User
    {
        return $this->associatedUser;
    }

    public function setAssociatedUser(?User $user): self
    {
        $this->associatedUser = $user;

        return $this;
    }
}

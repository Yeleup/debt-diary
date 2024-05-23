<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Repository\TransactionRepository;
use App\State\TransactionStateProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new Post(),
        new GetCollection(),
        new Delete()
    ],
    normalizationContext: ["groups" => ["transaction.read"]],
    denormalizationContext: ["groups" => ["transaction.write"]],
    order: ['createdAt' => 'DESC'],
    processor: TransactionStateProcessor::class
)]
#[ApiResource(
    uriTemplate: '/customers/{customerId}/transactions',
    operations: [ new GetCollection() ],
    uriVariables: [
        'customerId' => new Link(toProperty: 'customer', fromClass: Customer::class),
    ],
    normalizationContext: ["groups" => ["customer.transaction.read"]],
    denormalizationContext: ["groups" => ["customer.transaction.write"]],
    order: ['createdAt' => 'DESC'],
    paginationItemsPerPage: 10
)]
#[ApiFilter(DateFilter::class, properties: ["createdAt"])]
#[ApiFilter(OrderFilter::class, properties: ["createdAt"])]
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['transaction.read', 'customer.transaction.read'])]
    private $id;

    #[Groups(['transaction.read', 'transaction.write', 'customer.transaction.read'])]
    #[ORM\Column(type: 'float', precision: 10, scale: 0)]
    private ?float $amount;

    #[Groups(['transaction.write', 'transaction.read', 'customer.transaction.read'])]
    #[ORM\ManyToOne(targetEntity: Type::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private $type;

    #[Groups(['transaction.read', 'transaction.write', 'customer.transaction.read'])]
    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private $payment;

    #[Groups(['transaction.write', 'transaction.read', 'customer.transaction.read'])]
    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'transactions')]
    private $customer;

    #[ORM\Column(type: 'datetime', nullable: false, name: 'created_at')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'updated_at')]
    private ?\DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['transaction.read', 'customer.transaction.read'])]
    private $user;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $confirmed;

    #[Groups(['transaction.read', 'transaction.write', 'customer.transaction.read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

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

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(?bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function isConfirmed(): ?bool
    {
        return $this->confirmed;
    }
}

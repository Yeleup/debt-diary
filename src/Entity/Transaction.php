<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"transaction.read"}},
 *     denormalizationContext={"groups"={"transaction.write"}},
 *     collectionOperations={
 *          "get"={"normalization_context"={"groups"={"transaction.read", "transaction_detail.write"}}},
 *          "post"
 *     },
 *     itemOperations={
 *          "get"={"normalization_context"={"groups"={"transaction.read", "transaction_detail.write"}}}
 *     }
 * )
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"transaction.read"})
     */
    private $id;

    /**
     * @Groups({"transaction.read", "transaction.write"})
     * @ORM\Column(type="float", precision=10, scale=0)
     */
    private $amount;

    /**
     * @Groups({"transaction.write", "transaction.read"})
     * @ORM\ManyToOne(targetEntity=Type::class, inversedBy="transactions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $type;

    /**
     * @Groups({"transaction.read", "transaction.write"})
     * @ORM\ManyToOne(targetEntity=Payment::class, inversedBy="transactions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $payment;

    /**
     * @Groups({"transaction_detail.write", "transaction.write"})
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="transactions")
     */
    private $customer;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var DateTime
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $confirmed;

    /**
     * @Groups({"transaction.write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
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

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
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
}

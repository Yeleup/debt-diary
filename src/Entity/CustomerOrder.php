<?php

namespace App\Entity;

use App\Repository\CustomerOrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CustomerOrderRepository::class)
 */
class CustomerOrder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class, inversedBy="customerOrders")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Payment::class, inversedBy="customerOrders")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $payment;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="customerOrders")
     */
    private $customer;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var DateTime
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
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
        $this->payment = null;

        if ($this->getType()) {
            if ($this->getType()->getPaymentStatus()) {
                $this->payment = $payment;
            }
        }

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
}

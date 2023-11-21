<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiPlatform\Filter\CustomerFilter;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;

#[ApiResource(
    normalizationContext: ['groups' => ['customer.read']],
    denormalizationContext: ['groups' => ['customer.write']],
    order: ['place' => 'ASC']
)]
#[ApiFilter(CustomerFilter::class, properties: ['search' => SearchFilterInterface::STRATEGY_START])]
#[ApiFilter(OrderFilter::class, properties: ['place', 'name', 'total', 'last_transaction'])]
#[Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['customer.read', 'transaction.read', 'customer.transaction.read'])]
    private $id;

    #[Assert\NotBlank]
    #[Groups(['customer.read', 'customer.write', 'transaction.read', 'customer.transaction.read'])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['transaction.read', 'customer.read', 'customer.write'])]
    private ?string $place = null;

    #[Groups(['transaction.read', 'customer.read', 'customer.write'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $contact = null;

    #[Assert\NotBlank]
    #[Groups(['customer.read', 'customer.write'])]
    #[ORM\ManyToOne(targetEntity: Market::class, inversedBy: 'customers')]
    private ?Market $market = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Transaction::class, cascade: ['remove'])]
    #[Link(toProperty: 'customer')]
    private Collection $transactions;

    #[Groups(['customer.read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $total = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $last_transaction = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getMarket(): ?Market
    {
        return $this->market;
    }

    public function setMarket(?Market $market): self
    {
        $this->market = $market;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setCustomer($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCustomer() === $this) {
                $transaction->setCustomer(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return $this->name;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getLastTransaction(): ?\DateTimeInterface
    {
        return $this->last_transaction;
    }

    public function setLastTransaction(?\DateTimeInterface $last_transaction): self
    {
        $this->last_transaction = $last_transaction;

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\ApiPlatform\CustomerSearchFilter;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"customer.read"}},
 *     denormalizationContext={"groups"={"customer.write"}},
 *     itemOperations={
 *          "get",
 *          "patch"
 *     },
 *     collectionOperations={
 *          "get",
 *          "post"
 *     }
 * )
 * @ApiFilter(CustomerSearchFilter::class, properties={"search": SearchFilter::STRATEGY_START})
 * @ApiFilter(OrderFilter::class, properties={"place", "name", "total", "last_transaction"})
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"customer_order.read", "customer.read"})
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Groups({"customer_order.read", "customer.read", "customer.write"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"customer_order.read", "customer.read", "customer.write"})
     */
    private $place;

    /**
     * @Groups({"customer_order.read", "customer.read", "customer.write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @Assert\NotBlank()
     * @Groups({"customer.write"})
     * @ORM\ManyToOne(targetEntity=Market::class, inversedBy="customers")
     */
    private $market;

    /**
     * @ORM\OneToMany(targetEntity=CustomerOrder::class, mappedBy="customer", cascade={"remove"})
     * @ApiSubresource()
     */
    private $customerOrders;

    /**
     * @Groups({"customer.read"})
     * @ORM\Column(type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_transaction;

    public function __construct()
    {
        $this->customerOrders = new ArrayCollection();
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
     * @return Collection|CustomerOrder[]
     */
    public function getCustomerOrders(): Collection
    {
        return $this->customerOrders;
    }

    public function addCustomerOrder(CustomerOrder $customerOrder): self
    {
        if (!$this->customerOrders->contains($customerOrder)) {
            $this->customerOrders[] = $customerOrder;
            $customerOrder->setCustomer($this);
        }

        return $this;
    }

    public function removeCustomerOrder(CustomerOrder $customerOrder): self
    {
        if ($this->customerOrders->removeElement($customerOrder)) {
            // set the owning side to null (unless already changed)
            if ($customerOrder->getCustomer() === $this) {
                $customerOrder->setCustomer(null);
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

    public function setTotal(float $total): self
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

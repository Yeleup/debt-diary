<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\MarketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    itemOperations: ['get'],
    denormalizationContext: ['groups' => ['market.write']],
    normalizationContext: ['groups' => ['market.read']],
    paginationEnabled: false
)]
#[Entity(repositoryClass: MarketRepository::class)]
class Market
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    #[Groups(['market.read'])]
    private ?int $id = null;

    #[Column(type: 'string', length: 255)]
    #[Groups(['market.read', 'market.write'])]
    private ?string $title = null;

    #[OneToMany(targetEntity: Customer::class, mappedBy: 'market')]
    private Collection $customers;

    #[ManyToMany(targetEntity: User::class, mappedBy: 'markets')]
    private Collection $users;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return $this->title;
    }

    /**
     * @return Collection|Customer[]
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers[] = $customer;
            $customer->setMarket($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getMarket() === $this) {
                $customer->setMarket(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }
}

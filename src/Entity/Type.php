<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

#[ApiResource(
    collectionOperations: [
        'get' => ['normalization_context' => ['groups' => ['type.read']]]
    ],
    attributes: ['pagination_enabled' => false]
)]
#[ApiFilter(OrderFilter::class, properties: ['sort'])]
#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['customer.transaction.read', 'type.read'])]
    private $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['customer.transaction.read', 'type.read'])]
    private ?string $title = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Transaction::class)]
    private $transactions;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    #[Groups(['type.read'])]
    private ?string $prefix = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['type.read'])]
    private bool $payment_status;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['type.read'])]
    private ?int $sort = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['type.read'])]
    private ?string $color = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
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
            $transaction->setType($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getType() === $this) {
                $transaction->setType(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return $this->title;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPaymentStatus(): ?bool
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(bool $payment_status): self
    {
        $this->payment_status = $payment_status;

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}

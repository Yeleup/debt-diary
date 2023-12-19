<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExpenseTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ExpenseTypeRepository::class)]
#[ApiResource(
    shortName: 'Expense-Type',
    normalizationContext: ["groups" => ["expense_type.read"]],
    denormalizationContext: ["groups" => ["expense_type.write"]],
)]
class ExpenseType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['expense_type.read', 'expense.read', 'user.expense.read'])]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'expenseType', targetEntity: Expense::class, orphanRemoval: true)]
    private Collection $expenses;

    #[ORM\Column(length: 255)]
    #[Groups(['expense_type.read', 'expense_type.write', 'expense.read', 'user.expense.read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['expense_type.read', 'expense_type.write'])]
    private ?string $color = null;

    #[ORM\Column]
    #[Groups(['expense_type.read', 'expense_type.write'])]
    private ?bool $is_add_expense = true;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): static
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
            $expense->setExpenseType($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): static
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getExpenseType() === $this) {
                $expense->setExpenseType(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function isAddExpense(): ?bool
    {
        return $this->is_add_expense;
    }

    public function setAddExpense(bool $is_add_expense): static
    {
        $this->is_add_expense = $is_add_expense;

        return $this;
    }
}

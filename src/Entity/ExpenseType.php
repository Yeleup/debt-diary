<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\ApiPlatform\Filter\ExpenseTypeFilter;
use App\Enum\Mode;
use App\Repository\ExpenseTypeRepository;
use App\Validator\ExpenseType\ParentNotEqualId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExpenseTypeRepository::class)]
#[ApiResource(
    shortName: 'Expense-Type',
    normalizationContext: ["groups" => ["expense_type.read"]],
    denormalizationContext: ["groups" => ["expense_type.write"]],
    order: ['mode' => 'DESC', 'title' => 'ASC']
)]
#[ApiFilter(ExpenseTypeFilter::class, properties: ['search' => SearchFilterInterface::STRATEGY_START, 'parent' => SearchFilterInterface::STRATEGY_EXACT, 'mode' => SearchFilterInterface::STRATEGY_EXACT])]
#[ParentNotEqualId]
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

    #[ORM\Column('is_add_amount')]
    #[Groups(['expense_type.read', 'expense_type.write'])]
    private ?bool $addAmountToEmployee = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: "children")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "SET NULL")]
    #[Groups(['expense_type.read', 'expense_type.write', 'expense.read'])]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: ExpenseType::class, cascade: ['persist'])]
    private ?Collection $children;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: [Mode::FILE->value, Mode::FOLDER->value], message: "Choose a valid mode.")]
    #[ORM\Column(type: 'string')]
    #[Groups(['expense_type.read', 'expense_type.write'])]
    private string $mode;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function isAddAmountToEmployee(): ?bool
    {
        return $this->addAmountToEmployee;
    }

    public function setAddAmountToEmployee(bool $addAmountToEmployee): static
    {
        $this->addAmountToEmployee = $addAmountToEmployee;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, ExpenseType>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(ExpenseType $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(ExpenseType $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }
}

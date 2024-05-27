<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Api\Action\GetUserMe;
use App\Dto\UserMeResetPasswordDto;
use App\Repository\UserRepository;
use App\State\UserMeResetPasswordStateProcessor;
use App\State\UserMeStateProvider;
use App\State\UserStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            uriTemplate: '/users/me',
            status: 200,
            normalizationContext: ['groups' => 'user.me'],
            provider: UserMeStateProvider::class,
        ),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
        new Post(
            uriTemplate: '/users/me/change-password',
            status: 202,
            input: UserMeResetPasswordDto::class,
            processor: UserMeResetPasswordStateProcessor::class,
        ),
    ],
    normalizationContext: ["groups" => ["user.read"]],
    denormalizationContext: ["groups" => ["user.write"]],
    processor: UserStateProcessor::class
)]
#[Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'This username is already taken.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    #[Groups(['user.read', 'expense.read', 'user.expense.read', 'user.me'])]
    private ?int $id = null;

    #[Column(type: 'string', length: 180, unique: true)]
    #[Groups(['user.read', 'user.write', 'user.me'])]
    private string $username;

    #[Column(type: 'json')]
    #[Groups(['user.read', 'user.write', 'user.me'])]
    private array $roles = [];

    #[Column(type: 'string')]
    #[Groups(['user.write'])]
    private string $password;

    #[ManyToMany(targetEntity: Market::class, inversedBy: 'users', cascade: ['persist'])]
    #[Groups(['user.read', 'user.write', 'user.me'])]
    private Collection $markets;

    #[ManyToMany(targetEntity: Payment::class, inversedBy: 'users')]
    private Collection $payments;

    #[Column(name: 'full_name', type: 'string', length: 180, nullable: true)]
    #[Groups(['transaction.read', 'expense.read', 'user.expense.read', 'user.read', 'user.write', 'user.me'])]
    private ?string $fullName = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Expense::class, cascade: ['remove'])]
    #[Link(toProperty: 'user')]
    private Collection $expenses;

    #[ORM\OneToMany(mappedBy: 'associatedUser', targetEntity: Expense::class)]
    private Collection $associatedExpenses;

    #[ORM\Column(nullable: true)]
    #[Groups(['user.read', 'user.me'])]
    private ?float $expenseTotal = null;

    public function __construct()
    {
        $this->markets = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->expenses = new ArrayCollection();
        $this->associatedExpenses = new ArrayCollection();
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): void
    {
        $this->plainPassword = $password;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function __toString(): string
    {
        return $this->username;
    }

    /**
     * @return Collection|Market[]
     */
    public function getMarkets(): Collection
    {
        return $this->markets;
    }

    public function addMarket(Market $market): self
    {
        if (!$this->markets->contains($market)) {
            $this->markets[] = $market;
        }

        return $this;
    }

    public function removeMarket(Market $market): self
    {
        $this->markets->removeElement($market);

        return $this;
    }

    /**
     * @return Collection|Payment[]
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        $this->payments->removeElement($payment);

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getFullName(): ?string
    {
        return (string) $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return Collection|Expense[]
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setUser($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getUser() === $this) {
                $expense->setUser(null);
            }
        }

        return $this;
    }

    public function getExpenseTotal(): ?float
    {
        return $this->expenseTotal;
    }

    public function setExpenseTotal(?float $expenseTotal): static
    {
        $this->expenseTotal = $expenseTotal;

        return $this;
    }

    public function getAssociatedExpenses(): Collection
    {
        return $this->associatedExpenses;
    }
}

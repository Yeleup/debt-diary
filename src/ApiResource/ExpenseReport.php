<?php
namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\ExpenseReportProvider;

#[ApiResource(
    shortName: 'Expense-Report',
    operations: [
        new GetCollection()
    ],
    provider: ExpenseReportProvider::class,
)]
class ExpenseReport
{
    private ?int $id = null;
    private ?string $title = null;
    private ?float $amount = null;
    private array $children = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }
}

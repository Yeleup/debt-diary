<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ExpenseReport;
use App\Entity\ExpenseType;
use App\Repository\ExpenseRepository;
use App\Repository\ExpenseTypeRepository;

class ExpenseReportProvider implements ProviderInterface
{
    public function __construct(
        protected ExpenseTypeRepository $expenseTypeRepository,
        protected ExpenseRepository $expenseRepository,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $expenseTypes = $this->expenseTypeRepository->findBy(['parent' => null],[]);

        $expenseReports = [];
        foreach ($expenseTypes as $expenseType) {
            if ($expenseType->getParent() === null) {
                $report = $this->convertToExpenseReport($expenseType);
                $expenseReports[] = $report;
            }
        }

        return $expenseReports;
    }

    private function convertToExpenseReport(ExpenseType $expenseType): ExpenseReport
    {
        $report = new ExpenseReport();
        $report->setId($expenseType->getId());
        $report->setTitle($expenseType->getTitle());
        $amount = $this->expenseRepository->sumByExpenseType($expenseType);
        $amount += $this->calculateAmountForChildren($expenseType);
        $report->setAmount($amount);

        $childrenReports = [];
        foreach ($expenseType->getChildren() as $child) {
            $childrenReports[] = $this->convertToExpenseReport($child);
        }
        $report->setChildren($childrenReports);

        return $report;
    }

    private function calculateAmountForChildren(ExpenseType $expenseType): float
    {
        $amount = 0;
        foreach ($expenseType->getChildren() as $child) {
            $amount += $this->expenseRepository->sumByExpenseType($child);
            $amount += $this->calculateAmountForChildren($child); // Recursively calculate amount for grandchildren
        }
        return $amount;
    }
}

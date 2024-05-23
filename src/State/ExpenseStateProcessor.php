<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Expense;
use App\Repository\ExpenseRepository;
use Symfony\Component\Security\Core\Security;

class ExpenseStateProcessor implements ProcessorInterface
{

    public function __construct(
        protected ProcessorInterface $decorated,
        protected Security $security,
        protected ExpenseRepository $expenseRepository
    )
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Expense) {
            $data = $this->expenseRepository->plusOrMinusDependingType($data);
            $data->setUser($this->security->getUser());
            if ($data->getAssociatedUser()) {
                $newExpense = clone $data;
                $newExpense->setUser($data->getAssociatedUser());
                $newExpense->setAssociatedUser($this->security->getUser());
                $newExpense->setAmount(-1 * $data->getAmount());
                $this->expenseRepository->addExpense($newExpense);
            }
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}

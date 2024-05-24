<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Expense;
use App\Entity\User;
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
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        if ($data instanceof Expense) {
            $data->setUser($currentUser);
            $data = $this->expenseRepository->plusOrMinusDependingType($data, $currentUser);
            if ($data->getAssociatedUser()) {
                $newExpense = clone $data;
                $newExpense->setUser($data->getAssociatedUser());
                $newExpense->setAssociatedUser($this->security->getUser());
                $newExpense = $this->expenseRepository->plusOrMinusDependingType($newExpense, $currentUser);
                $this->decorated->process($newExpense, $operation, $uriVariables, $context);
            }
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}

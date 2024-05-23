<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
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
        $data = $this->expenseRepository->plusOrMinusDependingType($data);
        $data->setUser($this->security->getUser());
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}

<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\TransactionRepository;
use Symfony\Component\Security\Core\Security;

class TransactionStateProcessor implements ProcessorInterface
{

    public function __construct(
        protected ProcessorInterface $decorated,
        protected Security $security,
        protected TransactionRepository $transactionRepository
    )
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $data = $this->transactionRepository->plusOrMinusDependingType($data);

        $data->setUser($this->security->getUser());

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}

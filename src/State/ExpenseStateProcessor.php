<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Security\Core\Security;

class ExpenseStateProcessor implements ProcessorInterface
{

    public function __construct(protected ProcessorInterface $decorated, protected Security $security)
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $data->setUser($this->security->getUser());

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}

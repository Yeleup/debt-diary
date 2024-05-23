<?php

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class MarketStateProcessor implements ProcessorInterface
{
    public function __construct(
        protected ProcessorInterface $persistProcessor,
        protected ProcessorInterface $removeProcessor,
        protected Security $security,
    )
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($operation instanceof DeleteOperationInterface) {
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }
        /** @var User $user */
        $user = $this->security->getUser();
        $user->addMarket($data);
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}

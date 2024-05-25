<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\Security\Core\Security;

class UserMeStateProvider implements ProviderInterface
{
    public function __construct(
        protected ProviderInterface $itemProvider,
        protected Security $security
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->security->getUser();
    }
}

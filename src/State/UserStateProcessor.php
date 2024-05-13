<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserStateProcessor implements ProcessorInterface
{
    public function __construct(protected ProcessorInterface $decorated, protected UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPassword()));
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}

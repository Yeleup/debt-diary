<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UserMeResetPasswordDto;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @implements ProcessorInterface<UserMeResetPasswordDto, User>
 */
class UserMeResetPasswordStateProcessor implements ProcessorInterface
{
    public function __construct(
        protected ProcessorInterface $persistProcessor,
        protected Security $security,
        protected UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user && $data instanceof UserMeResetPasswordDto) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data->password));
            $user->eraseCredentials();
            return $this->persistProcessor->process($user, $operation, $uriVariables, $context);
        }
        return $this->persistProcessor->process($user, $operation, $uriVariables, $context);
    }
}

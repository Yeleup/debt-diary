<?php

namespace App\Validator\User;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsRoleControlValidator extends ConstraintValidator
{

    public function __construct(private Security $security)
    {
    }
    public function validate($value, Constraint $constraint): void
    {
        if ($this->security->isGranted('ROLE_CONTROL') && $value === null) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}

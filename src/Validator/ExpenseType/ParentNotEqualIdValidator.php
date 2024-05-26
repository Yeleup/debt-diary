<?php
namespace App\Validator\ExpenseType;

use App\Entity\ExpenseType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ParentNotEqualIdValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($value instanceof ExpenseType && $value->getId()) {
            if ($value->getId() === $value->getParent()->getId()) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
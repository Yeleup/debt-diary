<?php
namespace App\Validator\ExpenseType;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class ParentNotEqualId extends Constraint
{
    public string $message = 'The parent_id cannot be the same as the id.';

    public $groups = ['expense_type.write'];

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
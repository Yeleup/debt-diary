<?php
namespace App\Dto;
;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomerOrderInput
{
    /**
     * @Groups({"order:write"})
     */
    public $amount;

    /**
     * @Groups({"order:write"})
     */
    public $created;

    /**
     * @Groups({"order:write"})
     */
    public $updated;

    /**
     * @Groups({"order:write"})
     */
    public $customer;

    /**
     * @Groups({"order:write"})
     */
    public $payment;

    /**
     * @Groups({"order:write"})
     */
    public $type;
}
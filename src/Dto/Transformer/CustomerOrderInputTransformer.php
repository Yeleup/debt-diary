<?php


namespace App\Dto\Transformer;


use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\CustomerOrderInput;
use App\Entity\CustomerOrder;
use App\Entity\Payment;

class CustomerOrderInputTransformer implements DataTransformerInterface
{
    /**
     * Transforms the given object to something else, usually another object.
     * This must return the original object if no transformations have been done.
     *
     * @param object $object
     *
     * @return object
     */
    public function transform($object, string $to, array $context = []): CustomerOrder
    {
        $customerOrder = new CustomerOrder();

        $created = new \DateTime($object->created);
        $updated = new \DateTime($object->updated);

        return $customerOrder;
    }

    /**
     * Checks whether the transformation is supported for a given data and context.
     *
     * @param object|array $data object on normalize / array on denormalize
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof CustomerOrder) {
            return false;
        }

        return false;

        return CustomerOrder::class === $to && null !== ($context['input']['class'] ?? null);

    }
}
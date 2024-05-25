<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ExpenseTypeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(protected ObjectNormalizer $normalizer)
    {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $parent = $object->getParent();

        if ($parent) {
            $data['parent'] = sprintf('/api/expense-types/%d', $parent->getId());
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof \App\Entity\ExpenseType;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

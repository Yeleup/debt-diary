<?php

namespace App\Serializer\Normalizer;

use App\Service\MoneyFormatter;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $normalizer;
    private $moneyFormatter;

    public function __construct(ObjectNormalizer $normalizer, MoneyFormatter $moneyFormatter)
    {
        $this->normalizer = $normalizer;
        $this->moneyFormatter = $moneyFormatter;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['total'] = $this->moneyFormatter->format($object->getTotal());
        $data['lastTransactionAt'] = $object->getLastTransaction() ? $object->getLastTransaction()->format('d.m.y') : '';

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof \App\Entity\Customer;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

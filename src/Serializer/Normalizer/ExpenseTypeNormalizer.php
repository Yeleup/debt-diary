<?php

namespace App\Serializer\Normalizer;

use App\Entity\ExpenseType;
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
            $data['folderPath'] = $this->buildFolderPath($parent) . ' / ' . $object->getTitle();
        }

        return $data;
    }

    private function buildFolderPath(?ExpenseType $expenseType): string
    {
        $path = [];

        while ($expenseType) {
            $path[] = $expenseType->getTitle();
            $expenseType = $expenseType->getParent();
        }

        return implode(' / ', array_reverse($path));
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

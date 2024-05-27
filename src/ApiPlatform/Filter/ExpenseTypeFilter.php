<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class ExpenseTypeFilter extends AbstractFilter
{

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $alias = $queryBuilder->getRootAliases()[0];
        if ('search' == $property && !empty($value)) {
            $queryBuilder
                ->andWhere(sprintf('%s.title LIKE :searchValue', $alias))
                ->setParameter('searchValue', '%' . $value . '%');
        }

        if ('parent' == $property) {
            if (empty($value)) {
                $queryBuilder
                    ->andWhere(sprintf('%s.parent IS NULL', $alias));
            } else {
                $queryBuilder
                    ->andWhere(sprintf('%s.parent = :parentId', $alias))
                    ->setParameter('parentId', $value);
            }
        }

        if ('mode' == $property && !empty($value)) {
            $queryBuilder
                ->andWhere(sprintf('%s.mode = :mode', $alias))
                ->setParameter('mode', $value);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["$property"] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter with strategy: '.$strategy,
            ];
        }
        return $description;
    }
}
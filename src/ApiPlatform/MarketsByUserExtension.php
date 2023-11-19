<?php

namespace App\ApiPlatform;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Market;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class MarketsByUserExtension implements QueryCollectionExtensionInterface
{
    private Security $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($resourceClass !== Market::class) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        if ($this->security->getUser()) {
            $queryBuilder
                ->join(sprintf('%s.users', $alias), 'u')
                ->where('u = :user')
                ->setParameter('user', $this->security->getUser());
        }
    }
}
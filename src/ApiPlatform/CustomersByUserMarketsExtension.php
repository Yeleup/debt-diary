<?php

namespace App\ApiPlatform;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Customer;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CustomersByUserMarketsExtension implements QueryCollectionExtensionInterface
{
    private User $user;
    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($resourceClass !== Customer::class) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->join(sprintf('%s.market', $alias), 'm')
            ->join('m.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $this->user);
    }
}
<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\ResumableDataPersisterInterface;
use App\Entity\CustomerOrder;
use App\Repository\CustomerOrderRepository;
use Symfony\Component\Security\Core\Security;

final class CustomerOrderDataPersister implements ContextAwareDataPersisterInterface, ResumableDataPersisterInterface
{
    private Security $security;
    private CustomerOrderRepository $repository;
    public function __construct(Security $security, CustomerOrderRepository $repository)
    {
        $this->security = $security;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof CustomerOrder; // Add your custom conditions here
    }

    /**
     * {@inheritdoc}
     */
    public function resumable(array $context = []): bool
    {
        return true; // Set it to true if you want to call the other data persisters
    }

    /**
     * {@inheritdoc}
     */
    public function persist($data, array $context = []): CustomerOrder
    {
        /** @var CustomerOrder $customerOrder */
        $customerOrder = $data;

        $customerOrder = $this->repository->plusOrMinusDependingType($customerOrder);

        // Call your persistence layer to save $data
        if ($this->security->getUser()) {
            $customerOrder->setUser($this->security->getUser());
        }

        return $customerOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data, array $context = []): void
    {
        // Call your persistence layer to delete $data
    }
}

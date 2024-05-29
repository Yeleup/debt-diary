<?php

namespace App\EventListener;

use App\Entity\Customer;
use App\Entity\Expense;
use App\Entity\Transaction;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class EntityLifecycleListener implements EventSubscriber
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Transaction) {
            $customer = $entity->getCustomer();
            $this->entityManager->getRepository(Customer::class)->updateCustomerTotalAndLastTransaction($customer);
        }

        if ($entity instanceof Expense) {
            $user = $entity->getUser();
            $this->entityManager->getRepository(Expense::class)->updateUserExpenseTotal($user);
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Transaction) {
            $customer = $entity->getCustomer();
            $this->entityManager->getRepository(Customer::class)->updateCustomerTotalAndLastTransaction($customer);
        }

        if ($entity instanceof Expense) {
            $user = $entity->getUser();
            $this->entityManager->getRepository(Expense::class)->updateUserExpenseTotal($user);
        }
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Transaction) {
            $customer = $entity->getCustomer();
            $this->entityManager->getRepository(Customer::class)->updateCustomerTotalAndLastTransaction($customer);
        }

        if ($entity instanceof Expense) {
            $user = $entity->getUser();
            $this->entityManager->getRepository(Expense::class)->updateUserExpenseTotal($user);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }
}
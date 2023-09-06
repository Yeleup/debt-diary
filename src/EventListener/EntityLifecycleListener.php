<?php

namespace App\EventListener;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
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

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof CustomerOrder) {
            $customer = $entity->getCustomer();
            $this->entityManager->getRepository(Customer::class)->updateCustomerTotalAndLastTransaction($customer);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof CustomerOrder) {
            $customer = $entity->getCustomer();
            $this->entityManager->getRepository(Customer::class)->updateCustomerTotalAndLastTransaction($customer);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof CustomerOrder) {
            $customer = $entity->getCustomer();
            $this->entityManager->getRepository(Customer::class)->updateCustomerTotalAndLastTransaction($customer);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }
}
<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CustomerOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerOrder[]    findAll()
 * @method CustomerOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerOrder::class);
    }

    public function sumAmountByCustomer(Customer $customer)
    {
        $qb = $this->createQueryBuilder('co');

        return $qb->select('SUM(co.amount)')
            ->where('co.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function plusOrMinusDependingType(CustomerOrder $customerOrder)
    {
        if ($customerOrder->getType()) {
            $amount = (float) abs($customerOrder->getAmount());

            // Плюсуем или минусуем, смотря по префиксу
            if ($customerOrder->getType()->getPrefix() == '-') {
                $amount = -1 * $amount;
                $customerOrder->setAmount($amount);
            } else {
                $customerOrder->setAmount($amount);
            }

            // Не показываем оплату если в типах не указано
            if (!$customerOrder->getType()->getPaymentStatus()) {
                $customerOrder->setPayment(null);
            }
        }

        return $customerOrder;
    }

    public function addOrder(CustomerOrder $customerOrder)
    {
        $entityManager = $this->getEntityManager();
        $customerOrder = $this->plusOrMinusDependingType($customerOrder);
        $entityManager->persist($customerOrder);
        $entityManager->flush();
    }

    public function editOrder(CustomerOrder $customerOrder)
    {
        $entityManager = $this->getEntityManager();
        $customerOrder = $this->plusOrMinusDependingType($customerOrder);
        $entityManager->persist($customerOrder);
        $entityManager->flush();
    }

    public function deleteOrder(CustomerOrder $customerOrder)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($customerOrder);
        $entityManager->flush();
    }
}

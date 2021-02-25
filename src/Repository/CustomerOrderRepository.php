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

    public function getCustomerTotal(Customer $customer)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('SUM(c.amount) as total');
        $qb->where($qb->expr()->andX(
            $qb->expr()->eq('c.customer', ':customer_id')
        ));
        $qb->setParameter('customer_id', $customer->getId());

        $total = 0;

        if ($qb->getQuery()->getSingleResult()['total']) {
            $total = $qb->getQuery()->getSingleResult()['total'];
        }

        return $total;
    }

    public function addOrder(CustomerOrder $customerOrder)
    {
        $customer = $customerOrder->getCustomer();

        $entityManager = $this->getEntityManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $entityManager->persist($customerOrder);
            $entityManager->flush();

            // Общая сумма клиента
            $total = $this->getCustomerTotal($customer);
            $customer->setTotal($total);

            // Последняя оплата клиента, если приход
            if ($customerOrder->getPayment()) {
                $customer->setLastTransaction(new \DateTime());
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            $entityManager->getConnection()->commit();
        } catch (\Exception $exception) {
            $entityManager->getConnection()->rollBack();
            throw $exception;
        }
    }

    public function editOrder(CustomerOrder $customerOrder)
    {
        $customer = $customerOrder->getCustomer();

        $entityManager = $this->getEntityManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $entityManager->persist($customerOrder);
            $entityManager->flush();

            // Общая сумма клиента
            $total = $this->getCustomerTotal($customer);
            $customer->setTotal($total);

            // Последняя оплата клиента, если приход
            if ($customerOrder->getPayment()) {
                $customer->setLastTransaction(new \DateTime());
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            $entityManager->getConnection()->commit();
        } catch (\Exception $exception) {
            $entityManager->getConnection()->rollBack();
            throw $exception;
        }
    }

    public function deleteOrder(CustomerOrder $customerOrder)
    {
        $customer = $customerOrder->getCustomer();

        $entityManager = $this->getEntityManager();

        $entityManager->getConnection()->beginTransaction();
        try {
            $entityManager->remove($customerOrder);
            $entityManager->flush();

            // Общая сумма клиента
            $total = $this->getCustomerTotal($customer);
            $customer->setTotal($total );
            $entityManager->persist($customer);
            $entityManager->flush();

            $entityManager->getConnection()->commit();
        } catch (\Exception $exception) {
            $entityManager->getConnection()->rollBack();
            throw $exception;
        }
    }
}

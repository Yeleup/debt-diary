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

    public function checkOrder(CustomerOrder $customerOrder)
    {
        $result = $this->createQueryBuilder('c');
        $result->select('count(c) as counter')
            ->where($result->expr()->andX(
                $result->expr()->eq('c.amount', ':amount'),
                $result->expr()->eq('c.customer', ':customer'),
                $result->expr()->eq('c.user', ':user'),
                $result->expr()->eq('c.created', ':created')
            ));

        if ($customerOrder->getType()) {
            $result->andWhere($result->expr()->andX($result->expr()->eq('c.type', ':type')));
            $result->setParameter('type', $customerOrder->getType());
        }

        if ($customerOrder->getPayment()) {
            $result->andWhere($result->expr()->andX($result->expr()->eq('c.payment', ':payment')));
            $result->setParameter('payment', $customerOrder->getPayment());
        }

        $result->setParameter('amount', $customerOrder->getAmount());
        $result->setParameter('customer', $customerOrder->getCustomer());
        $result->setParameter('user', $customerOrder->getUser());
        $result->setParameter('created', $customerOrder->getCreated());

        return $result->getQuery()->getSingleResult();
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

<?php

namespace App\Repository;

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

    public function getPreviousOrder(CustomerOrder $order)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')

            // Filter users.
            ->where('c.id < :order')
            ->andWhere('c.customer = :customer')
            ->setParameter(':order', $order)
            ->setParameter(':customer', $order->getCustomer())

            // Order by id.
            ->orderBy('c.id', 'DESC')

            // Get the first record.
            ->setFirstResult(0)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getNextOrder(CustomerOrder $order)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')

            // Filter users.
            ->where('c.id > :order')
            ->andWhere('c.customer = :customer')
            ->setParameter(':order', $order)
            ->setParameter(':customer', $order->getCustomer())

            // Order by id.
            ->orderBy('c.id', 'ASC')

            // Get the first record.
            ->setFirstResult(0)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function checkOrder(CustomerOrder $customerOrder)
    {
        // Плюсуем или минусуем, смотря по префиксу
        if ($customerOrder->getType()) {
            $amount = (float) abs($customerOrder->getAmount());
            if ($customerOrder->getType()->getPrefix() == '-') {
                $amount = -1 * $amount;
                $customerOrder->setAmount($amount);
            } else {
                $customerOrder->setAmount($amount);
            }
        }

        $criteria = [
            'amount' => $customerOrder->getAmount(),
            'customer' => $customerOrder->getCustomer(),
            'user' => $customerOrder->getUser(),
            'created' => $customerOrder->getCreated(),
        ];

        if ($customerOrder->getType()) {
            $criteria['type'] = $customerOrder->getType();
        }

        if ($customerOrder->getPayment()) {
            $criteria['payment'] = $customerOrder->getPayment();
        }

        return $this->findOneBy($criteria);
    }

    public function addOrder(CustomerOrder $customerOrder)
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

        $customer = $customerOrder->getCustomer();

        $entityManager = $this->getEntityManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $entityManager->persist($customerOrder);
            $entityManager->flush();

            // Предыдущий заказ
            $previousOrder = $this->getPreviousOrder($customerOrder);

            // Общая сумма клиента
            if ($previousOrder) {
                $total = ($previousOrder->getTotal() + $customerOrder->getAmount());
                $customerOrder->setTotal($total);
                $customer->setTotal($total);
            } else {
                $customerOrder->setTotal($customerOrder->getAmount());
                $customer->setTotal($customerOrder->getAmount());
            }

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

        $customer = $customerOrder->getCustomer();

        $entityManager = $this->getEntityManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $entityManager->persist($customerOrder);
            $entityManager->flush();

            // Предыдущий заказ
            $previousOrder = $this->getPreviousOrder($customerOrder);

            // Общая сумма клиента
            if ($previousOrder) {
                $total = ($previousOrder->getTotal() + $customerOrder->getAmount());
                $customerOrder->setTotal($total);
                $customer->setTotal($total);
            } else {
                $customerOrder->setTotal($customerOrder->getAmount());
                $customer->setTotal($customerOrder->getAmount());
            }

            // Последняя оплата клиента, если приход
            if ($customerOrder->getPayment()) {
                $customer->setLastTransaction(new \DateTime());
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            $entityManager->getConnection()->commit();

            // Получаем следующий заказ
            $nextOrder = $this->getNextOrder($customerOrder);

            // Рекурсивно изменяем следующие заказы
            if ($nextOrder) {
                $this->editOrder($nextOrder);
            }
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

            // Предыдущий заказ
            $previousOrder = $this->getPreviousOrder($customerOrder);

            // Общая сумма клиента
            if ($previousOrder) {
                $total = ($previousOrder->getTotal() + $customerOrder->getAmount());
                $customerOrder->setTotal($total);
                $customer->setTotal($total);
            } else {
                $customerOrder->setTotal($customerOrder->getAmount());
                $customer->setTotal($customerOrder->getAmount());
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            $entityManager->getConnection()->commit();

            // Получаем следующий заказ
            $nextOrder = $this->getNextOrder($customerOrder);

            // Рекурсивно изменяем следующие заказы
            if ($nextOrder) {
                $this->editOrder($nextOrder);
            }
        } catch (\Exception $exception) {
            $entityManager->getConnection()->rollBack();
            throw $exception;
        }
    }

    public function getByDate(\DateTime $date, $user, $customer = 0)
    {
        $from = new \DateTime($date->format('Y-m-d').' 00:00:00');
        $to = new \DateTime($date->format('Y-m-d').' 23:59:59');

        $qb = $this->createQueryBuilder('e');
        $qb
            ->andWhere('e.user = :user')
            ->andWhere('e.created BETWEEN :from AND :to')
            ->setParameter('user', $user)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        if ($customer) {
            $qb->andWhere('e.customer = :customer')->setParameter('customer', $customer);
        }

        $qb->orderBy('e.updated', 'ASC');

        $result = $qb->getQuery()->getResult();

        return $result;
    }
}

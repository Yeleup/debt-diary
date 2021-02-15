<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Form\CustomerOrderType;
use App\Repository\CustomerOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/customer_order", name="admin_customer_order")
 */
class CustomerOrderController extends AbstractController
{

    /**
     * @Route("/list/{id}", name="_list", requirements={"id"="\d+"})
     */
    public function index(Customer $customer, CustomerOrderRepository $customerOrderRepository)
    {
        $customer_orders = $customerOrderRepository->findBy(['customer' => $customer], ['updated' => 'ASC']);

        return $this->render('admin/customer_order/index.html.twig', [
            'customer' => $customer,
            'customer_orders' => $customer_orders,
        ]);
    }

    /**
     * @Route("/new/{id}", name="_new", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function new(Request $request, Customer $customer)
    {
        $customerOrder = new CustomerOrder();
        $customerOrder->setCustomer($customer);
        $customerOrder->setUser($this->getUser());
        $form = $this->createForm(CustomerOrderType::class, $customerOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerOrder->setUser($this->getUser());

            // Плюсуем или минусуем, смотря по префиксу
            if ($customerOrder->getType()) {
                if ($customerOrder->getType()->getPrefix() == '-') {
                    $customerOrder->setAmount((float) ('-'.abs($customerOrder->getAmount())));
                } else {
                    $customerOrder->setAmount((float) (abs($customerOrder->getAmount())));
                }
            }

            // Добавляем заказ пользователя
            $customerOrder->setCustomer($customer);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($customerOrder);
            $entityManager->flush();

            // Общая сумма клиента
            $total = $this->getDoctrine()->getRepository(CustomerOrder::class)->getCustomerTotal($customer);
            $customer->setTotal($total);

            // Последняя оплата клиента, если приход
            if ($customerOrder->getPayment()) {
                $customer->setLastTransaction(new \DateTime());
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('admin_customer_order_list', ['id'=> $customer->getId(), 'eaContext' => $request->query->get('eaContext')]);
        }

        return $this->render('admin/customer_order/new.html.twig', [
            'customer' => $customer,
            'customer_order' => $customerOrder,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function edit(Request $request, CustomerOrder $customerOrder)
    {
        $customer = $customerOrder->getCustomer();

        $form = $this->createForm(CustomerOrderType::class, $customerOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerOrder->setUser($this->getUser());

            // Плюсуем или минусуем, смотря по префиксу
            if ($customerOrder->getType()) {
                if ($customerOrder->getType()->getPrefix() == '-') {
                    $customerOrder->setAmount((float) ('-'.abs($customerOrder->getAmount())));
                } else {
                    $customerOrder->setAmount((float) (abs($customerOrder->getAmount())));
                }
            }

            // Добавляем заказ пользователя
            $customerOrder->setCustomer($customer);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($customerOrder);
            $entityManager->flush();

            // Общая сумма клиента
            $total = $this->getDoctrine()->getRepository(CustomerOrder::class)->getCustomerTotal($customer);
            $customer->setTotal($total);

            // Последняя оплата клиента, если приход
            if ($customerOrder->getPayment()) {
                $customer->setLastTransaction(new \DateTime());
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('admin_customer_order_list', ['id'=> $customer->getId(), 'eaContext' => $request->query->get('eaContext')]);
        }

        return $this->render('admin/customer_order/edit.html.twig', [
            'customer' => $customer,
            'customer_order' => $customerOrder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CustomerOrder $customerOrder): Response
    {

        $customer = $customerOrder->getCustomer();

        if ($this->isCsrfTokenValid('delete'.$customerOrder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($customerOrder);
            $entityManager->flush();

            // Общая сумма клиента
            $total = $this->getDoctrine()->getRepository(CustomerOrder::class)->getCustomerTotal($customer);
            $customer->setTotal($total );
            $entityManager->persist($customer);
            $entityManager->flush();
        }

        if ($request->query->get('eaContext')) {
            return $this->redirectToRoute('admin_customer_order_list', ['id'=> $customer->getId(), 'eaContext' => $request->query->get('eaContext')]);
        }
    }
}

<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Type;
use App\Form\CustomerOrderType;
use App\Repository\CustomerOrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @Route("/customer_order", name="customer_order")
 */
class CustomerOrderController extends AbstractController
{
    private $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    /**
     * @Route("/{id}", name="_index", requirements={"id"="\d+"})
     */
    public function index(Customer $customer, CustomerOrderRepository $customerOrderRepository)
    {
        // Text
        $lang['user'] = new TranslatableMessage('customer_order.user');
        $lang['created'] = new TranslatableMessage('customer_order.created');
        $lang['type'] = new TranslatableMessage('customer_order.type');
        $lang['payment'] = new TranslatableMessage('customer_order.payment');
        $lang['amount'] = new TranslatableMessage('customer_order.amount');
        $lang['action'] = new TranslatableMessage('customer_order.action');
        $lang['edit'] = new TranslatableMessage('edit');
        $lang['delete'] = new TranslatableMessage('delete');
        $lang['add'] = new TranslatableMessage('add');
        $lang['return'] = new TranslatableMessage('return');
        $lang['no_records_found'] = new TranslatableMessage('no_records_found');

        $customer_orders = $customerOrderRepository->findBy(['customer' => $customer], ['updated' => 'ASC']);

        return $this->render('customer_order/index.html.twig', [
            'customer' => $customer,
            'customer_orders' => $customer_orders,
            'lang' => $lang,
        ]);
    }

    /**
     * @Route("/new/{id}", name="_new", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function new(Request $request, Customer $customer)
    {
        // Text
        $lang['add'] = new TranslatableMessage('add');
        $lang['return'] = new TranslatableMessage('return');

        $customerOrder = new CustomerOrder();
        $customerOrder->setCustomer($customer);
        $customerOrder->setUser($this->getUser());
        $form = $this->createForm(CustomerOrderType::class, $customerOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerOrder->setUser($this->getUser());

            // Добавляем заказ пользователя
            $customerOrder->setCustomer($customer);

            // Добавления реализации
            $this->getDoctrine()->getRepository(CustomerOrder::class)->addOrder($customerOrder);

            return $this->redirect($this->adminUrlGenerator->setRoute('customer_order_index', ['id'=> $customer->getId()])->generateUrl());
        }

        $types = $this->getDoctrine()->getRepository(Type::class)->findAll();

        $data['types'] = [];

        foreach ($types as $type) {
            $data['types'][] = array(
                'id' => $type->getId(),
                'payment_status' => $type->getPaymentStatus(),
            );
        }

        return $this->render('customer_order/new.html.twig', [
            'customer' => $customer,
            'data' => $data,
            'customer_order' => $customerOrder,
            'form' => $form->createView(),
            'lang' => $lang,
        ]);
    }


    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function edit(Request $request, CustomerOrder $customerOrder)
    {
        // Text
        $lang['save'] = new TranslatableMessage('save');
        $lang['return'] = new TranslatableMessage('return');

        $customer = $customerOrder->getCustomer();

        $form = $this->createForm(CustomerOrderType::class, $customerOrder)->remove('updated');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerOrder->setUser($this->getUser());

            // Добавляем заказ пользователя
            $customerOrder->setCustomer($customer);

            // Редактирование реализации
            $this->getDoctrine()->getRepository(CustomerOrder::class)->editOrder($customerOrder);

            return $this->redirect($this->adminUrlGenerator->setRoute('customer_order_index', ['id'=> $customer->getId()])->generateUrl());
        }

        $types = $this->getDoctrine()->getRepository(Type::class)->findAll();

        $data['types'] = [];

        foreach ($types as $type) {
            $data['types'][] = array(
               'id' => $type->getId(),
               'payment_status' => $type->getPaymentStatus(),
            );
        }

        return $this->render('customer_order/edit.html.twig', [
            'customer' => $customer,
            'data' => $data,
            'customer_order' => $customerOrder,
            'form' => $form->createView(),
            'lang' => $lang,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CustomerOrder $customerOrder): Response
    {

        $customer = $customerOrder->getCustomer();

        if ($this->isCsrfTokenValid('delete'.$customerOrder->getId(), $request->request->get('_token'))) {
            $this->getDoctrine()->getRepository(CustomerOrder::class)->deleteOrder($customerOrder);
        }

        return $this->redirect($this->adminUrlGenerator->setRoute('customer_order_index', ['id'=> $customer->getId()])->generateUrl());
    }
}

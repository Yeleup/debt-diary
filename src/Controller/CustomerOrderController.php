<?php

namespace App\Controller;

use App\Controller\Admin\CustomerCrudController;
use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Type;
use App\Form\CustomerOrderType;
use App\Repository\CustomerOrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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
    public function index(Request $request, Customer $customer, CustomerOrderRepository $customerOrderRepository)
    {
        // GET
        $params = [];
        if ($request->query->get('market')) {
            $params['market'] = $request->query->get('market');
        }

        if ($request->query->get('search')) {
            $params['search'] = $request->query->get('search');
        }

        if ($request->query->get('order')) {
            $params['order'] = $request->query->get('order');
        }

        if ($request->query->get('sorting')) {
            $params['sorting'] = $request->query->get('sorting');
        }

        if ($request->query->get('page')) {
            $params['page'] = $request->query->get('page');
        }

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
        $lang['add'] = new TranslatableMessage('add');
        $lang['back'] = new TranslatableMessage('back');
        $lang['no_records_found'] = new TranslatableMessage('no_records_found');

        $data['customer_orders'] = array();
        $customer_orders = $customerOrderRepository->findBy(['customer' => $customer], ['created' => 'ASC']);

        foreach ($customer_orders as $customerOrder) {

            if ($customer->getMarket()) {
                $params['market'] = $customer->getMarket()->getId();
            }

            if ($this->isGranted("ROLE_ADMIN")) {
                $edit = $this->adminUrlGenerator->setRoute("customer_order_edit", ['id' => $customerOrder->getId()])->generateUrl();
                $delete = $this->adminUrlGenerator->setRoute("customer_order_delete", ['id' => $customerOrder->getId()])->generateUrl();
            } else {
                $edit = $this->adminUrlGenerator->setRoute("customer_order_edit", ['id' => $customerOrder->getId()])
                    ->setAll($params)
                    ->generateUrl();

                $delete = $this->adminUrlGenerator->setRoute("customer_order_delete", ['id' => $customerOrder->getId()])
                    ->setAll($params)
                    ->generateUrl();
            }

            $data['customer_orders'][] = array(
                'id' => $customerOrder->getId(),
                'user' => $customerOrder->getUser(),
                'updated' => $customerOrder->getUpdated(),
                'type' => $customerOrder->getType(),
                'payment' => $customerOrder->getPayment(),
                'amount' => $customerOrder->getAmount(),
                'total' => $customerOrder->getTotal(),
                'edit' => $edit,
                'delete' => $delete,
            );
        }


        if ($this->isGranted("ROLE_ADMIN")) {
            $link['add'] = $this->adminUrlGenerator->setRoute('customer_order_new', ['id' => $customer->getId()])->generateUrl();
            $link['edit'] = $this->adminUrlGenerator->setRoute('customer_order_index', ['id' => $customer->getId()])->generateUrl();
            $link['back'] = $this->adminUrlGenerator->setController(CustomerCrudController::class)->setAction('index')->generateUrl();
        } else {
            $link['add'] = $this->adminUrlGenerator->setRoute('customer_order_new', ['id' => $customer->getId()])
                ->setAll($params)
                ->generateUrl();

            $link['edit'] = $this->adminUrlGenerator->setController(CustomerCrudController::class)
                ->setEntityId($customer->getId())
                ->setAction(Action::EDIT)
                ->setAll($params)
                ->includeReferrer()
                ->generateUrl();
            
            $link['back'] = $this->adminUrlGenerator->setRoute('user_customer', ['id' => $request->query->get('market')])
                ->setAll($params)
                ->generateUrl();
        }

        if ($this->isGranted("ROLE_USER")) {
            $render = $this->render('user/customer_order/index.html.twig', [
                'link' => $link,
                'customer' => $customer,
                'customer_orders' => $data['customer_orders'],
                'lang' => $lang,
            ]);
        } else {
            $render = $this->render('customer_order/index.html.twig', [
                'link' => $link,
                'customer' => $customer,
                'customer_orders' => $data['customer_orders'],
                'lang' => $lang,
            ]);
        }

        return $render;
    }

    /**
     * @Route("/new/{id}", name="_new", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function new(Request $request, Customer $customer)
    {
        // GET
        $params = [];
        if ($customer->getMarket()) {
            $params['market'] = $customer->getMarket()->getId();
        }

        if ($request->query->get('search')) {
            $params['search'] = $request->query->get('search');
        }

        if ($request->query->get('order')) {
            $params['order'] = $request->query->get('order');
        }

        if ($request->query->get('sorting')) {
            $params['sorting'] = $request->query->get('sorting');
        }

        if ($request->query->get('page')) {
            $params['page'] = $request->query->get('page');
        }

        // Text
        $lang['create'] = new TranslatableMessage('create');
        $lang['return'] = new TranslatableMessage('return');

        $customerOrder = new CustomerOrder();
        $customerOrder->setCustomer($customer);
        $customerOrder->setUser($this->getUser());
        $form = $this->createForm(CustomerOrderType::class, $customerOrder);

        $form->add('updated', DateTimeType::class, [
            'label_format' => new TranslatableMessage('customer_order.updated'),
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'yyyy-MM-dd HH:mm:ss',
            'attr' => ['class' => 'js-datepicker'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerOrder->setUser($this->getUser());

            // Добавляем заказ пользователя
            $customerOrder->setCustomer($customer);

            // Добавления реализации
            $this->getDoctrine()->getRepository(CustomerOrder::class)->addOrder($customerOrder);

            if ($this->isGranted("ROLE_ADMIN")) {
                $redirect = $this->redirect($this->adminUrlGenerator->setRoute('customer_order_index', ['id'=> $customer->getId()])->generateUrl());
            } else {
                $redirect = $this->redirect(
                    $this->adminUrlGenerator
                        ->setRoute('customer_order_index', ['id'=> $customer->getId()])
                        ->setAll($params)
                        ->generateUrl()
                );
            }

            return $redirect;
        }

        $types = $this->getDoctrine()->getRepository(Type::class)->findAll();

        $data['types'] = [];

        foreach ($types as $type) {
            $data['types'][] = array(
                'id' => $type->getId(),
                'payment_status' => $type->getPaymentStatus(),
            );
        }

        if ($this->isGranted("ROLE_ADMIN")) {
            $link['return'] = $this->adminUrlGenerator->setRoute('customer_order_index', ['id' => $customer->getId()])->generateUrl();

        } else {
            $link['return'] = $this->adminUrlGenerator->setRoute('customer_order_index',  ['id' => $customer->getId()])
                ->setAll($params)
                ->generateUrl();
        }

        return $this->render('customer_order/new.html.twig', [
            'link' => $link,
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
        $customer = $customerOrder->getCustomer();

        // GET
        $params = [];
        if ($customer->getMarket()) {
            $params['market'] = $customer->getMarket()->getId();
        }

        if ($request->query->get('search')) {
            $params['search'] = $request->query->get('search');
        }

        if ($request->query->get('order')) {
            $params['order'] = $request->query->get('order');
        }

        if ($request->query->get('sorting')) {
            $params['sorting'] = $request->query->get('sorting');
        }

        if ($request->query->get('page')) {
            $params['page'] = $request->query->get('page');
        }

        // Text
        $lang['save'] = new TranslatableMessage('save');
        $lang['return'] = new TranslatableMessage('return');

        $form = $this->createForm(CustomerOrderType::class, $customerOrder)->remove('updated');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerOrder->setUser($this->getUser());

            // Добавляем заказ пользователя
            $customerOrder->setCustomer($customer);

            // Редактирование реализации
            $this->getDoctrine()->getRepository(CustomerOrder::class)->editOrder($customerOrder);

            if ($this->isGranted("ROLE_ADMIN")) {
                $redirect = $this->redirect($this->adminUrlGenerator->setRoute('customer_order_index', ['id'=> $customer->getId()])->generateUrl());
            } else {
                $redirect = $this->redirect(
                    $this->adminUrlGenerator
                        ->setRoute('customer_order_index', ['id'=> $customer->getId()])
                        ->setAll($params)
                        ->generateUrl()
                );
            }

            return $redirect;
        }

        $types = $this->getDoctrine()->getRepository(Type::class)->findAll();

        $data['types'] = [];

        foreach ($types as $type) {
            $data['types'][] = array(
               'id' => $type->getId(),
               'payment_status' => $type->getPaymentStatus(),
            );
        }

        if ($this->isGranted("ROLE_ADMIN")) {
            $link['return'] = $this->adminUrlGenerator->setRoute('customer_order_index', ['id' => $customer->getId()])->generateUrl();

        } else {
            $link['return'] = $this->adminUrlGenerator->setRoute('customer_order_index',  ['id' => $customer->getId()])
                ->setAll($params)
                ->generateUrl();
        }

        return $this->render('customer_order/edit.html.twig', [
            'link' => $link,
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

        if ($this->isGranted("ROLE_ADMIN")) {
            $return = $this->redirect($this->adminUrlGenerator->setRoute('customer_order_index', ['id'=> $customer->getId()])->generateUrl());
        } else {
            $return = $this->redirect($this->adminUrlGenerator->setRoute('customer_order_index', ['id'=> $customer->getId()])
                ->set('market', $customer->getMarket()->getId())
                ->set('search', $request->query->get('search'))
                ->set('order', $request->query->get('order'))
                ->set('sorting', $request->query->get('sorting'))
                ->set('page', $request->query->get('page'))
                ->generateUrl());
        }

        return $return;
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Type;
use App\Form\CustomerOrderType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function showPayment(AdminContext $context)
    {
        $json['success'] = false;
        if ($context->getRequest()->get('type')) {
            $json['success'] = $this->getDoctrine()->getRepository(Type::class)->find($context->getRequest()->get('type'))->getPaymentStatus();
        }
        return new JsonResponse($json);
    }

    public function showMore(AdminContext $context)
    {
        if ($context->getRequest()->get('offset')) {
            $offset = $context->getRequest()->get('offset');
            $customer_id = $context->getRequest()->get('entityId');
            $results = array_reverse($this->getDoctrine()->getRepository(CustomerOrder::class)->findBy(['customer' => $customer_id], ['id' => 'DESC'], 5, $offset));
            $orders = array();
            foreach ($results as $result) {
                $orders[] = array(
                    'id' => $result->getId(),
                    'type_prefix' => $result->getType()->getPrefix(),
                    'type_title' => $result->getType()->getTitle(),
                    'payment_title' => ($result->getPayment() != null ? $result->getPayment()->getTitle() : ''),
                    'amount' => $result->getAmount(),
                    'created' => $result->getCreated()->format('d.m.Y'),
                );
            }
            return new JsonResponse($orders);
        }
        return new JsonResponse(array());
    }

    public function detail(AdminContext $context)
    {
        $responseParameters = parent::detail($context);

        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            //Customer
            $customer = $this->getDoctrine()->getRepository(Customer::class)->find($responseParameters->get('entity')->getPrimaryKeyValue());

            //Order List
            $orders = array_reverse($this->getDoctrine()->getRepository(CustomerOrder::class)->findBy(['customer' => $customer->getId()], ['id' => 'DESC'], 5, 0));

            //Form Added
            $customerOrder = new CustomerOrder();
            $form = $this->createFormBuilder($customerOrder)
                ->add('amount')
                ->add('type')
                ->add('payment')
                ->getForm();
            $responseParameters->set('form', $form);

            $form->handleRequest($context->getRequest());

            if ($form->isSubmitted() && $form->isValid()) {
                $customerOrder->setUser($this->getUser());

                //Плюсуем или минусуем, смотря по префиксу
                if ($customerOrder->getType()) {
                    if ($customerOrder->getType()->getPrefix() == '-') {
                        $customerOrder->setAmount((float) ('-'.abs($customerOrder->getAmount())));
                    } else {
                        $customerOrder->setAmount((float) (abs($customerOrder->getAmount())));
                    }
                }

                //Добавляем заказ пользователя
                $customerOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($customerOrder);
                $entityManager->flush();

                //Общая сумма
                $total = $this->getDoctrine()->getRepository(CustomerOrder::class)->getCustomerTotal($customer);
                $customer->setTotal($total);

                //Последняя оплата клиента
                if ($customerOrder->getPayment()) {
                    $customer->setLastTransaction(new \DateTime());
                }

                $entityManager->persist($customer);
                $entityManager->flush();

                return $this->redirect($this->get(CrudUrlGenerator::class)->build()->generateUrl());
            }

            $responseParameters->set('form', $form->createView());
            $responseParameters->set('customer', $customer);
            $responseParameters->set('orders', $orders);
        }

        return $responseParameters;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($searchDto->getRequest()->get('market')) {
            $queryBuilder->andWhere('entity.market = ' . (int) $searchDto->getRequest()->get('market'));
        }

        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE,'ROLE_ADMIN')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/crud/customer_detail.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('place'),
            TextField::new('contact'),
            AssociationField::new('market'),
            NumberField::new('total')->onlyOnIndex(),
            DateField::new('last_transaction')->onlyOnIndex(),
        ];
    }
}

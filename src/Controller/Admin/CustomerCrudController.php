<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Type;
use App\Form\CustomerOrderType;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
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

                $customerOrder->setCustomer($customer);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($customerOrder);
                $entityManager->flush();

                return $this->redirect($this->get(CrudUrlGenerator::class)->build()->generateUrl());
            }

            $responseParameters->set('form', $form->createView());
            $responseParameters->set('orders', $orders);
        }

        return $responseParameters;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
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
            AssociationField::new('market')
        ];
    }
}

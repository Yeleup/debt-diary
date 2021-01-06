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

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function detail(AdminContext $context)
    {
        $responseParameters = parent::detail($context);

        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            //Customer
            $customer = $this->getDoctrine()->getRepository(Customer::class)->find($responseParameters->get('entity')->getPrimaryKeyValue());

            //Order List
            $responseParameters->set('orders', $this->getDoctrine()->getRepository(Customer::class)->find($responseParameters->get('entity')->getPrimaryKeyValue())->getCustomerOrders());

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

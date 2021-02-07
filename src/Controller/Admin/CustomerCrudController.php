<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
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
        $customerOrder = Action::new('customerOrder', 'История заказов')->linkToRoute('admin_customer_order_list', function (Customer $customer): array {return ['id' => $customer->getId()];});
        return $actions
            ->setPermission(Action::DELETE,'ROLE_ADMIN')
            ->add(Crud::PAGE_INDEX, $customerOrder)
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
            DateField::new('last_transaction')->setFormat('y-MM-d H:m:s')->onlyOnIndex(),
        ];
    }
}

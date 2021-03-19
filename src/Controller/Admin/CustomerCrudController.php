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
        $user_id = $this->getUser()->getId();

        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($this->isGranted("ROLE_USER")) {
            $queryBuilder->join('entity.market', 'm');
            $queryBuilder->join('m.users', 'u');
            $queryBuilder->andWhere(':user MEMBER OF m.users')->setParameter('user', $user_id);
        }

        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $customerOrder = Action::new('customerOrder', 'customer.history')->linkToRoute('customer_order_index', function (Customer $customer): array {return ['id' => $customer->getId()];});

        if (!$this->getUser()->getMarkets()->toArray()) {
            $actions->setPermission(Action::NEW,'ROLE_ADMIN');
        }

        return $actions
            ->setPermission(Action::DELETE,'ROLE_ADMIN')
            ->add(Crud::PAGE_INDEX, $customerOrder);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/crud/customer_detail.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $users = $this->getUser();

        if ($this->isGranted("ROLE_USER")) {
            $marketField = AssociationField::new('market','customer.market')->setFormTypeOptions(["choices" => $users->getMarkets()->toArray()]);
        } else {
            $marketField = AssociationField::new('market','customer.market');
        }

        if ($pageName == 'index') {
            $marketField = TextField::new('market','customer.market');
        }

        return [
            TextField::new('name','customer.name'),
            TextField::new('place','customer.place'),
            TextField::new('contact','customer.contact'),
            $marketField,
            NumberField::new('total','customer.total')->onlyOnIndex(),
            DateField::new('last_transaction','customer.last_transaction')->setFormat('y-MM-dd HH:mm:ss')->onlyOnIndex(),
        ];
    }
}

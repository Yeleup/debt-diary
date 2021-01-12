<?php

namespace App\Controller\Admin;

use App\Entity\CustomerOrder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerOrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerOrder::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('amount'),
            AssociationField::new('payment'),
            AssociationField::new('type'),
            AssociationField::new('customer'),
            AssociationField::new('user'),
        ];
    }

}

<?php

namespace App\Controller\Admin;

use App\Entity\Market;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MarketCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Market::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}

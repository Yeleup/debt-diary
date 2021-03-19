<?php

namespace App\Controller\Admin;

use App\Entity\Market;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class MarketCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Market::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title')->setLabel('market.title'),
        ];
    }
}

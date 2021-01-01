<?php

namespace App\Controller\Admin;

use App\Entity\Type;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Type::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            ChoiceField::new('prefix')->setChoices(['minus' => '-', 'plus' => '+']),
        ];
    }
}

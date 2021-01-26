<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class UserCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = parent::configureFields($pageName);
        $fields[] = AssociationField::new('markets')->onlyOnForms();
        $fields[] = ChoiceField::new('roles')
            ->setChoices(['Администратор' => 'ROLE_ADMIN', 'Пользователь' => 'ROLE_USER'])
            ->allowMultipleChoices(true);

        return array_map(function ($f) use ($pageName) {
            if ($f->getAsDto()->getProperty() === 'password') {
                $field = TextField::new('plain_password', Crud::PAGE_NEW === $pageName ? 'Password' : 'Change password')
                    ->setFormType(PasswordType::class);
                if (Crud::PAGE_NEW === $pageName) {
                    $field->setRequired(true);
                }
                return $field;
            }
            return $f;
        }, $fields);
    }
}

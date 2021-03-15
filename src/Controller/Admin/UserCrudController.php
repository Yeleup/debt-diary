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
        $fields[] = TextField::new('username', 'user.username');
        $fields[] = AssociationField::new('markets', 'user.market')->onlyOnForms();
        $fields[] = ChoiceField::new('roles', 'user.role')
            ->setChoices(['user.role_admin' => 'ROLE_ADMIN', 'user.role_user' => 'ROLE_USER'])
            ->allowMultipleChoices(true);

        $fields[] = AssociationField::new('payments')->setLabel('user.payment');

        return array_map(function ($f) use ($pageName) {
            if ($f->getAsDto()->getProperty() === 'password') {
                $field = TextField::new('plain_password', Crud::PAGE_NEW === $pageName ? 'user.password' : 'user.change_password')
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

<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserCrudController extends AbstractCrudController implements EventSubscriberInterface
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

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

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => 'encodePassword',
            BeforeEntityUpdatedEvent::class => 'encodePassword',
        ];
    }

    /** @internal */
    public function encodePassword($event)
    {
        if ($this::getEntityFqcn() == User::class) {
            $user = $event->getEntityInstance();
            if ($user->getPlainPassword()) {
                $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            }

            if ($user->getMarkets()) {
                foreach ($user->getMarkets()->toArray() as $item) {
                    $item->setUser($user);
                }
            }
        }
    }
}

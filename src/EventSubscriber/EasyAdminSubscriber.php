<?php

namespace App\EventSubscriber;

use App\Entity\Customer;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $passwordEncoder;
    private $flashBag;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, FlashBagInterface $flashBag)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->flashBag = $flashBag;
    }

    public function BeforeEntityEvent($event)
    {
        // События при добавления пользователя
        $instance = $event->getEntityInstance();

        if (($instance instanceof User) && $instance->getPlainPassword()) {
            $instance->setPassword($this->passwordEncoder->encodePassword($instance, $instance->getPlainPassword()));
        }


        // События при добавления клиента
        if ($instance instanceof Customer) {
            $this->flashBag->add('success', '<b>'. $instance->getName() .'</b> успешно добавлен или изменен');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => 'BeforeEntityEvent',
            BeforeEntityUpdatedEvent::class => 'BeforeEntityEvent',
        ];
    }
}

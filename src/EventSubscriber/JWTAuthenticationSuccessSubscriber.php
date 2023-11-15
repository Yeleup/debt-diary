<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTAuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = [];
        foreach ($event->getData() as $key => $value) {
            $data[$key] = $value;
        }

        $user = $event->getUser();
        if ($user instanceof User) {
            $data['fullName'] = $user->getFullName();
            $data['roles'] = $user->getRoles();
        }

        $event->setData($data);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }
}

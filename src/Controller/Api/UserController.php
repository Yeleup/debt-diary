<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route(name="api_user", path="/api/user", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=User::class,
     *      "_api_collection_operation_name"="get"
     *     }
     * )
     */
    public function __invoke()
    {
        // Виды оплаты
        $payments = [];

        if (!empty( $this->getUser()->getPayments())) {
            foreach ($this->getUser()->getPayments()->toArray() as $payment) {
                $payments[] = array(
                    'id' => $payment->getId(),
                    'title' => $payment->getTitle(),
                );
            }
        }

        // Точки продаж
        $markets = [];

        if (!empty( $this->getUser()->getMarkets())) {
            foreach ($this->getUser()->getMarkets()->toArray() as $market) {
                $markets[] = array(
                    'id' => $market->getId(),
                    'title' => $market->getTitle(),
                );
            }
        }

        $user = array(
            'id' => $this->getUser()->getId(),
            'username' => $this->getUser()->getUsername(),
            'role' => $this->getUser()->getRoles(),
            'payments' => $payments,
            'markets' => $markets,
        );

        return new JsonResponse($user);
    }
}

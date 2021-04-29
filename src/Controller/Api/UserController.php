<?php

namespace App\Controller\Api;

use App\Entity\CustomerOrder;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route(name="api_get_current_orders", path="/api/user/current_orders", methods={"GET"},
     * defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="get_current_orders"}
     * )
     */
    public function getCurrentOrders()
    {
        $orders = array();

        if ($this->getUser()) {
            $now = new \DateTime();

            $repo = $this->getDoctrine()->getRepository(CustomerOrder::class);

            $currentOrders = $repo->getByDate($now, $this->getUser());

            foreach ($currentOrders as $order) {
                $attr = array(
                    'amount' => $order->getAmount(),
                    'created' => ($order->getCreated() ? $order->getCreated()->format('Y-m-d H:i:s') : ''),
                    'updated' => ($order->getUpdated() ? $order->getUpdated()->format('Y-m-d H:i:s') : ''),
                );

                if ($order->getType()) {
                    $attr['type'] = $order->getType()->getId();
                }

                if ($order->getPayment()) {
                    $attr['payment'] = $order->getPayment()->getId();
                }

                if ($order->getCustomer()) {
                    $attr['customer'] = $order->getCustomer()->getId();
                }

                if ($order->getConfirmed()) {
                    $attr['confirmed'] = $order->getConfirmed();
                }

                $orders[] = $attr;
            }
        }
        return new JsonResponse($orders);
    }

    /**
     * @Route(name="api_get_current_user", path="/api/user", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=User::class,
     *      "_api_collection_operation_name"="get_current_user"
     *     }
     * )
     */
    public function getCurrentUser()
    {
        $user = array(
            'id' => $this->getUser()->getId(),
            'username' => $this->getUser()->getUsername(),
            'role' => $this->getUser()->getRoles(),
        );

        // Виды оплаты
        if ($this->isGranted("ROLE_CONTROL")) {
            $payments = [];

            if (!empty($this->getUser()->getPayments())) {
                foreach ($this->getUser()->getPayments()->toArray() as $payment) {
                    $payments[] = array(
                        'id' => $payment->getId(),
                        'title' => $payment->getTitle(),
                    );
                }
            }

            $user['payments'] = $payments;
        }

        // Точки продаж
        if ($this->isGranted("ROLE_USER")) {
            $markets = [];

            if (!empty($this->getUser()->getMarkets())) {
                foreach ($this->getUser()->getMarkets()->toArray() as $market) {
                    $markets[] = array(
                        'id' => $market->getId(),
                        'title' => $market->getTitle(),
                    );
                }
            }

            $user['markets'] = $markets;
        }

        return new JsonResponse($user);
    }
}

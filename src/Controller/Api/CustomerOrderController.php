<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerOrderController extends AbstractController
{
    /**
     * @Route(
     *     name="api_customer_order_collection",
     *     path="/api/customer_order",
     *     methods={"POST"},
     *     defaults={
     *         "_api_resource_class"=CustomerOrder::class,
     *         "_api_collection_operation_name"="post"
     *     }
     * )
     */
    public function postCustomerOrder(CustomerOrder $data): Response
    {
        $user = $this->getUser();
        if ($data->getCustomer()) {
            $data->setUser($user);

            $checkOrder = $this->getDoctrine()->getRepository(CustomerOrder::class)->checkOrder($data);
            if (!$checkOrder) {
                $this->getDoctrine()->getRepository(CustomerOrder::class)->addOrder($data);

                return new JsonResponse(['status' => 'success', 'text' => 'success', 'customer' => $data->getCustomer()->getId(), 'total' => $data->getCustomer()->getTotal()], Response::HTTP_OK, []);
            } else {
                return new JsonResponse(['status' => 'error', 'text' => 'duplicate', 'customer' => $data->getCustomer()->getId(), 'total' => $data->getCustomer()->getTotal()], Response::HTTP_BAD_REQUEST, []);
            }
        } else {
            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST, []);
        }
    }

    /**
     * @Route(name="api_get_customer_order", path="/api/customer_order/{id}", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=Customer::class,
     *      "_api_item_operation_name"="get_customer_order"
     *     }
     * )
     */
    public function getCustomerOrder(Customer $data): Response
    {
        $orders = [];

        if ($data) {
            $repo = $this->getDoctrine()->getRepository(CustomerOrder::class);

            $currentOrders = $repo->findBy(['customer' => $data], ['updated' => 'ASC']);

            foreach ($currentOrders as $order) {
                $attr = [
                    'username' => $order->getUser()->getUsername(),
                    'amount' => $order->getAmount(),
                    'created' => ($order->getCreated() ? $order->getCreated()->format('Y-m-d H:i:s') : ''),
                    'updated' => ($order->getUpdated() ? $order->getUpdated()->format('Y-m-d H:i:s') : ''),
                ];

                if ($order->getType()) {
                    $attr['type']['id'] = $order->getType()->getId();
                    $attr['type']['title'] = $order->getType()->getTitle();
                }

                if ($order->getPayment()) {
                    $attr['payment']['id'] = $order->getPayment()->getId();
                    $attr['payment']['title'] = $order->getPayment()->getTitle();
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
     * @Route(name="api_get_customer_order_current", path="/api/customer_order/{id}/current", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=Customer::class,
     *      "_api_item_operation_name"="get_customer_order_current"
     *     }
     * )
     */
    public function getCustomerOrderCurrent(Customer $data): Response
    {
        $orders = [];

        if ($data) {
            $now = new \DateTime();

            $repo = $this->getDoctrine()->getRepository(CustomerOrder::class);

            $currentOrders = $repo->getByDate($now, $this->getUser(), $data);

            foreach ($currentOrders as $order) {
                $attr = [
                    'username' => $order->getUser()->getUsername(),
                    'amount' => $order->getAmount(),
                    'created' => ($order->getCreated() ? $order->getCreated()->format('Y-m-d H:i:s') : ''),
                    'updated' => ($order->getUpdated() ? $order->getUpdated()->format('Y-m-d H:i:s') : ''),
                ];

                if ($order->getType()) {
                    $attr['type']['id'] = $order->getType()->getId();
                    $attr['type']['title'] = $order->getType()->getTitle();
                }

                if ($order->getPayment()) {
                    $attr['payment']['id'] = $order->getPayment()->getId();
                    $attr['payment']['title'] = $order->getPayment()->getTitle();
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
}

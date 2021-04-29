<?php

namespace App\Controller\Api;

use App\Entity\CustomerOrder;
use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Handler\CustomerOrderPublishingHandler;

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
    public function postCustomerOrder(CustomerOrder $data)
    {
        $user = $this->getUser();
        if ($data->getCustomer()) {
            $data->setUser($user);

            $checkOrder = $this->getDoctrine()->getRepository(CustomerOrder::class)->checkOrder($data);
            if (!$checkOrder) {
                $this->getDoctrine()->getRepository(CustomerOrder::class)->addOrder($data);
                return new JsonResponse(['success' => 'Success'], 200,[]);
            } else {
                return new JsonResponse(['error' => 'Dupicate'], 400,[]);
            }
        } else {
            return new JsonResponse(['error' => 'Error'], 400,[]);
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
    public function getCustomerOrder(Customer $data)
    {
        $orders = array();

        if ($data) {
            $repo = $this->getDoctrine()->getRepository(CustomerOrder::class);

            $currentOrders = $repo->findBy(['customer' => $data]);

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
}

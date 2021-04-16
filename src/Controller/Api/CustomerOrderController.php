<?php

namespace App\Controller\Api;

use App\Entity\CustomerOrder;
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
        if ($data->getCustomer() && $data->getUser()) {
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
}

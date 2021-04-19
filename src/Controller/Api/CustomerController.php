<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    /**
     * @Route(name="api_get_customer", path="/api/customer/{id}", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=Customer::class,
     *      "_api_item_operation_name"="get_customer"
     *     }
     * )
     */
    public function getCustomer(Customer $data)
    {
        $customer_orders = [];
        if ($data->getCustomerOrders()) {
            foreach ($data->getCustomerOrders()->toArray() as $customer_order) {
                $customer_orders[] = array(
                    'amount' => $customer_order->getAmount(),
                    'type' => ($customer_order->getType() ? $customer_order->getType()->getId() : 'null'),
                    'payment' => ($customer_order->getPayment() ? $customer_order->getPayment()->getId() : 'null'),
                    'created' => ($customer_order->getCreated() ? $customer_order->getCreated()->format('Y-m-d H:i:s') : ''),
                    'updated' => ($customer_order->getUpdated() ? $customer_order->getUpdated()->format('Y-m-d H:i:s') : ''),
                    'confirmed' => $customer_order->getConfirmed(),
                );
            }
        }

        $json['name'] = $data->getName();
        $json['place'] = $data->getPlace();
        $json['contact'] = $data->getContact();
        $json['customer_order'] = $customer_orders;
        $json['market_id'] = ($data->getMarket() ? $data->getMarket()->getId() : '');
        $json['total'] = $data->getTotal();
        $json['lastTransaction'] = ($data->getLastTransaction() ? $data->getLastTransaction()->format('Y-m-d H:i:s') : '');

        return new JsonResponse($json);
    }

    /**
     * @Route(
     *     name="api_post_customer",
     *     path="/api/customer",
     *     methods={"POST"},
     *     defaults={
     *         "_api_resource_class"=Customer::class,
     *         "_api_collection_operation_name"="post"
     *     }
     * )
     */
    public function postCustomer(Customer $data)
    {
        $repository =$this->getDoctrine()->getRepository(Customer::class);

        if ($data->getMarket()) {
            $check = $repository->checkCustomer($data);

            if (!$check) {
                $repository->addCustomer($data);
                return new JsonResponse(['success' => 'Success'], 200,[]);
            } else {
                return new JsonResponse(['success' => 'Dupicate'], 200,[]);
            }
        } else {
            return new JsonResponse(['error' => 'Error'], 200,[]);
        }
    }
}

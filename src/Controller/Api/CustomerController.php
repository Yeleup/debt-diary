<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
    public function getCustomer(Customer $data): Response
    {
        $customer_orders = [];
        if ($data->getCustomerOrders()) {
            foreach ($data->getCustomerOrders()->toArray() as $customer_order) {
                $customer_orders[] = [
                    'username' => $customer_order->getUser()->getUsername(),
                    'amount' => $customer_order->getAmount(),
                    'type' => ($customer_order->getType() ? $customer_order->getType()->getId() : 'null'),
                    'payment' => ($customer_order->getPayment() ? $customer_order->getPayment()->getId() : 'null'),
                    'created' => ($customer_order->getCreated() ? $customer_order->getCreated()->format('Y-m-d H:i:s') : ''),
                    'updated' => ($customer_order->getUpdated() ? $customer_order->getUpdated()->format('Y-m-d H:i:s') : ''),
                    'confirmed' => $customer_order->getConfirmed(),
                ];
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
    public function postCustomer(Customer $data): Response
    {
        $repository = $this->getDoctrine()->getRepository(Customer::class);

        if ($data->getMarket()) {
            $check = $repository->checkCustomer($data);

            if (!$check) {
                // Add Customer
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();

                return new JsonResponse(['success' => 'Success'], Response::HTTP_OK, []);
            } else {
                return new JsonResponse(['success' => 'Dupicate'], Response::HTTP_OK, []);
            }
        } else {
            return new JsonResponse(['error' => 'Error'], Response::HTTP_OK, []);
        }
    }

    /**
     * @Route(
     *     name="api_patch_customer",
     *     path="/api/customer/{id}",
     *     methods={"PATCH"},
     *     defaults={
     *         "_api_resource_class"=Customer::class,
     *         "_api_item_operation_name"="patch"
     *     }
     * )
     */
    public function patchCustomer(Customer $data): Response
    {
        // Update Customer
        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();

        return new JsonResponse(['success' => 'Success'], Response::HTTP_OK, []);
    }
}

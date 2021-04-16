<?php

namespace App\Controller\Api;

use App\Entity\Market;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MarketController extends AbstractController
{
    /**
     * @Route(name="api_get_market", path="/api/market/{id}", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=Market::class,
     *      "_api_item_operation_name"="get_market"
     *     }
     * )
     */
    public function getMarket(Market $data)
    {
        $market = [];
        $market['id'] = $data->getId();
        $market['title'] = $data->getTitle();

        // Customers
        if ($data->getCustomers()) {
            $customers = [];
            foreach ($data->getCustomers()->toArray() as $customer) {
                $customers[] = array(
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'place' => $customer->getPlace(),
                    'contact' => $customer->getContact(),
                    'total' => $customer->getTotal(),
                    'lastTransaction' => $customer->getLastTransaction(),
                );
            }
        }

        $market['customers'] = $customers;

        // Users
        if ($data->getUsers()) {
            $users = [];
            foreach ($data->getUsers()->toArray() as $user) {
                $users[] = array(
                    'id' => $user->getId()
                );
            }
        }

        $market['users'] = $users;

        return new JsonResponse($market);
    }
}

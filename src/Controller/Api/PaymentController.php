<?php

namespace App\Controller\Api;

use App\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    /**
     * @Route(name="api_get_payments", path="/api/payments", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=Payment::class,
     *      "_api_collection_operation_name"="get_payments"
     *     }
     * )
     */
    public function getPayments($data)
    {
        $payments = [];
        if (!empty($data)) {
            foreach ($data as $payment) {
                $payments[] = array(
                    'id' => $payment->getId(),
                    'title' => $payment->getTitle(),
                );
            }
        }

        return new JsonResponse($payments);
    }
}

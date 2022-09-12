<?php

namespace App\Controller\Api;

use App\Entity\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TypeController extends AbstractController
{
    /**
     * @Route(name="api_get_types", path="/api/types", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=Type::class,
     *      "_api_collection_operation_name"="get_types"
     *     }
     * )
     */
    public function getTypes($data): Response
    {
        $types = [];
        if (!empty($data)) {
            foreach ($data as $type) {
                $types[] = [
                    'id' => $type->getId(),
                    'title' => $type->getTitle(),
                    'prefix' => $type->getPrefix(),
                    'paymentStatus' => $type->getPaymentStatus(),
                ];
            }
        }

        return new JsonResponse($types);
    }
}

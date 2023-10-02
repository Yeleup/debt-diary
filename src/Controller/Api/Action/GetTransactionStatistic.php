<?php
namespace App\Controller\Api\Action;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Repository\TypeRepository;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetTransactionStatistic
{
    private $repository;
    private $typeRepository;

    public function __construct(TransactionRepository $repository, TypeRepository $typeRepository)
    {
        $this->repository = $repository;
        $this->typeRepository = $typeRepository;
    }

    /**
     * @Route(name="api_get_statistic", path="/api/transactions/statistic", methods={"GET"},
     * defaults={
     *      "_api_resource_class"=Transaction::class,
     *      "_api_collection_operation_name"="get_statistic"
     *     }
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        $endDate = Carbon::createFromFormat('Y-m-d', $request->query->get('endDate'))->endOfDay();

        $types = $this->typeRepository->createQueryBuilder('t')
            ->select('t.id', 't.title', 'SUM(transaction.amount) as value')
            ->join('t.transactions', 'transaction')
            ->where('transaction.createdAt BETWEEN :startDate AND :endDate')
            ->groupBy('t.id')
            ->setParameter('startDate', $request->query->get('startDate'))
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult()
        ;

        return new JsonResponse($types);
    }
}
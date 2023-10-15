<?php

namespace App\Controller\Api\Action;

use App\Entity\Transaction;
use App\Repository\MarketRepository;
use App\Repository\TypeRepository;
use App\Service\MoneyFormatter;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetTransactionStatistic
{
    private TypeRepository $typeRepository;
    private MarketRepository $marketRepository;
    private MoneyFormatter $moneyFormatter;

    public function __construct(TypeRepository $typeRepository, MarketRepository $marketRepository, MoneyFormatter $moneyFormatter)
    {
        $this->typeRepository = $typeRepository;
        $this->marketRepository = $marketRepository;
        $this->moneyFormatter = $moneyFormatter;
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

        $markets = $this->marketRepository->findAll();

        $data = [];
        foreach ($markets as $market) {
            $types = $this->typeRepository->createQueryBuilder('t')
                ->select('t.id', 't.title', 'SUM(transaction.amount) as value')
                ->join('t.transactions', 'transaction')
                ->join('transaction.customer', 'customer')
                ->join('customer.market', 'market')
                ->where('transaction.createdAt BETWEEN :startDate AND :endDate AND market.id = :market_id')
                ->groupBy('t.id')
                ->setParameter('startDate', $request->query->get('startDate'))
                ->setParameter('endDate', $endDate)
                ->setParameter('market_id', $market->getId())
                ->getQuery()
                ->getResult();

            $statistics = [];
            foreach ($types as $statistic) {
                $statistics[] = array(
                    'id' => $statistic['id'],
                    'title' => $statistic['title'],
                    'value' => $this->moneyFormatter->format($statistic['value']),
                );
            }

            $data[] = array(
                'id' => $market->getId(),
                'title' => $market->getTitle(),
                'statistics' => $statistics,
            );
        }

        return new JsonResponse($data);
    }
}
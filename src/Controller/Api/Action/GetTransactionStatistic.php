<?php

namespace App\Controller\Api\Action;

use App\Entity\Transaction;
use App\Repository\MarketRepository;
use App\Repository\PaymentRepository;
use App\Repository\TypeRepository;
use App\Service\MoneyFormatter;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class GetTransactionStatistic
{
    private Security $security;
    private TypeRepository $typeRepository;
    private MarketRepository $marketRepository;
    private PaymentRepository $paymentRepository;
    private MoneyFormatter $moneyFormatter;

    public function __construct(
        Security $security,
        TypeRepository $typeRepository,
        MarketRepository $marketRepository,
        PaymentRepository $paymentRepository,
        MoneyFormatter $moneyFormatter)
    {
        $this->typeRepository = $typeRepository;
        $this->marketRepository = $marketRepository;
        $this->moneyFormatter = $moneyFormatter;
        $this->paymentRepository = $paymentRepository;
        $this->security = $security;
    }

    #[Route(path: '/api/transactions/statistic', name: 'api_get_statistic', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $startDate = null;
        if ($request->query->get('startDate')) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->query->get('startDate'))->startOfDay();
        }

        $endDate = null;
        if ($request->query->get('endDate')) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->query->get('endDate'))->endOfDay();
        }

        $markets = $this->marketRepository->createQueryBuilder('m')
            ->join('m.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $this->security->getUser())
            ->getQuery()
            ->getResult()
        ;

        $data = [];
        foreach ($markets as $market) {
            $qb = $this->typeRepository->createQueryBuilder('t');
            $qb->select('t.id', 't.title', 't.payment_status', 'SUM(transaction.amount) as value')
                ->join('t.transactions', 'transaction')
                ->join('transaction.customer', 'customer')
                ->join('customer.market', 'market')
                ->where('market.id = :market_id')
                ->setParameter('market_id', $market->getId());

            if ($startDate) {
                $qb->andWhere('transaction.createdAt > :startDate')->setParameter('startDate', $startDate);
            }

            if ($endDate) {
                $qb->andWhere('transaction.createdAt < :endDate')->setParameter('endDate', $endDate);
            }

            $types = $qb->groupBy('t.id')->getQuery()->getResult();

            $statistics = [];
            $value = 0;
            foreach ($types as $type) {
                $qb = $this->paymentRepository->createQueryBuilder('p');
                $qb->select('p.id', 'p.title', 'SUM(transaction.amount) as value')
                    ->join('p.transactions', 'transaction')
                    ->join('transaction.type', 'type')
                    ->join('transaction.customer', 'customer')
                    ->join('customer.market', 'market')
                    ->where('type.id = :typeId')
                    ->andWhere('market.id = :marketId')
                    ->setParameter('typeId', $type['id'])
                    ->setParameter('marketId', $market->getId());

                if ($startDate) {
                    $qb->andWhere('transaction.createdAt > :startDate')->setParameter('startDate', $startDate);
                }

                if ($endDate) {
                    $qb->andWhere('transaction.createdAt < :endDate')->setParameter('endDate', $endDate);
                }

                $payments = $qb->groupBy('p.id')->getQuery()->getResult();

                $statisticPayments = [];
                foreach ($payments as $payment) {
                    $statisticPayments[] = array(
                        'id' => $payment['id'],
                        'title' => $payment['title'],
                        'value' => $this->moneyFormatter->format($payment['value']),
                    );
                }

                $value += $type['value'];

                $statistics[] = array(
                    'id' => $type['id'],
                    'title' => $type['title'],
                    'value' => $this->moneyFormatter->format($type['value']),
                    'payments' => $statisticPayments
                );
            }

            if ($statistics) {
                $data[] = array(
                    'id' => $market->getId(),
                    'title' => $market->getTitle(),
                    'value' => $this->moneyFormatter->format($value),
                    'statistics' => $statistics,
                );
            }
        }

        return new JsonResponse($data);
    }
}
<?php

namespace App\Controller\User;

use App\Entity\Customer;
use App\Entity\Market;
use App\Repository\MarketRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

class CustomerController extends AbstractController
{
    private $repository;
    private $adminUrlGenerator;

    public function __construct( MarketRepository $repository, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->repository = $repository;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    /**
     * @Route("user/customer/{id}", name="user_customer")
     */
    public function index(Request $request, Market $market, PaginatorInterface $paginator): Response
    {
        $customerRepository = $this->getDoctrine()->getRepository(Customer::class);

        // GET
        $params = [];
        if ($market) {
            $params['market'] = $market->getId();
        }

        if ($request->query->get('search')) {
            $params['search'] = $request->query->get('search');
        }

        if ($request->query->get('order')) {
            $params['order'] = $request->query->get('order');
        }

        if ($request->query->get('sorting')) {
            $params['sorting'] = $request->query->get('sorting');
        }

        if ($request->query->get('page')) {
            $params['page'] = $request->query->get('page');
        }

        // Список клиентов
        $filter_data = array(
            'market'             => $market,
            'search'             => $request->query->get('search'),
            'sort'               => $request->query->get('sorting'),
            'order'              => $request->query->get('order'),
        );

        $pagination = $customers = $paginator->paginate(
            $customerRepository->findByFilter($filter_data),
            $request->query->getInt('page', $request->query->get('page')),
            5
        );

        $data['customer'] = array();

        foreach ($customers as $customer) {
            $href = $this->adminUrlGenerator->setRoute('customer_order_index', ['id' => $customer->getId()])
                ->setAll($params)
                ->generateUrl();

            $data['customer'][] = array(
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'place' => $customer->getPlace(),
                'contact' => $customer->getContact(),
                'total' => $customer->getTotal(),
                'lastTransaction' => $customer->getLastTransaction(),
                'href' => $href,
            );
        }

        $lang['add'] = new TranslatableMessage('add');
        $lang['edit'] = new TranslatableMessage('edit');
        $lang['customerHistory'] = new TranslatableMessage('customer.history');

        // Список сортировки
        $sorts = array();

        $sorts[] = array(
            'text'  => 'По сумме реализации',
            'value' => 'c.total',
        );

        $sorts[] = array(
            'text'  => 'По дате оплаты',
            'value' => 'c.last_transaction',
        );

        $referer = $this->adminUrlGenerator->setRoute('user_customer', ['id' => $market->getId()])->generateUrl();


        return $this->render('user/customer/index.html.twig', [
            'referer' => $referer,
            'pagination' => $pagination,
            'lang' => $lang,
            'sorts' => $sorts,
            'market' => $market,
            'customers' => $data['customer'],
        ]);
    }
}

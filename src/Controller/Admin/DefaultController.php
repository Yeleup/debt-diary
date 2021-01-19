<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Market;
use App\Entity\Payment;
use App\Entity\Type;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {

        return Dashboard::new()
            ->setTitle('Tender');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Market', 'fas fa-list', Market::class)->setPermission("ROLE_ADMIN");

        // Market
        $markets = $this->getUser()->getMarkets()->toArray();

        $subitems = array();
        foreach ($markets as $item) {
            $subitems[] = MenuItem::linkToCrud($item->getTitle(), 'fas fa-users', Customer::class)->setQueryParameter('market', $item->getId());
        }

        $market = MenuItem::subMenu('Магазины','fas fa-list')->setSubItems($subitems);
        yield $market;


        yield MenuItem::linkToCrud('Все Покупатели', 'fas fa-users', Customer::class)->setPermission("ROLE_ADMIN");
        yield MenuItem::linkToCrud('CustomerOrder', 'fas fa-list', CustomerOrder::class)->setPermission("ROLE_ADMIN");
        yield MenuItem::linkToCrud('Payment', 'fas fa-list', Payment::class)->setPermission("ROLE_ADMIN");
        yield MenuItem::linkToCrud('Type', 'fas fa-list', Type::class)->setPermission("ROLE_ADMIN");
        yield MenuItem::linkToCrud('User', 'fas fa-user', User::class)->setPermission("ROLE_ADMIN");
    }
}

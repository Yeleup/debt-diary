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
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->generateUrl('user');
        }

        return Dashboard::new()
            ->setTitle('Tender');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Market', 'fas fa-list', Market::class);
        yield MenuItem::linkToCrud('Customer', 'fas fa-list', Customer::class);
        yield MenuItem::linkToCrud('CustomerOrder', 'fas fa-list', CustomerOrder::class);
        yield MenuItem::linkToCrud('Payment', 'fas fa-list', Payment::class);
        yield MenuItem::linkToCrud('Type', 'fas fa-list', Type::class);
        yield MenuItem::linkToCrud('User', 'fas fa-user', User::class);
    }
}

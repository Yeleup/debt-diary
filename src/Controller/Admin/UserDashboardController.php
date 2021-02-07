<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserDashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin/user", name="admin_user_dashboard")
     */
    public function index(): Response
    {
        if ($this->isGranted("ROLE_USER")) {
            $routeBuilder = $this->get(CrudUrlGenerator::class)->build();

            return $this->redirect($routeBuilder->setController(CustomerCrudController::class)->generateUrl());
        }

        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Қарыздар кітапшасы')
            ->renderSidebarMinimized(true);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Главная', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}

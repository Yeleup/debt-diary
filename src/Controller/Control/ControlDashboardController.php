<?php

namespace App\Controller\Control;

use App\Controller\Admin\CustomerOrderCrudController;
use App\Entity\CustomerOrder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ControlDashboardController extends AbstractDashboardController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/control", name="control")
     */
    public function index(): Response
    {
        if ($this->isGranted("ROLE_CONTROL")) {
            $routeBuilder = $this->get(CrudUrlGenerator::class)->build();

            return $this->redirect($routeBuilder->setController(CustomerOrderCrudController::class)->generateUrl());
        }

        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        $title = $this->translator->trans('header.name');
        return Dashboard::new()->setTitle($title);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('dashboard.order', 'fas fa-shopping-cart', CustomerOrder::class);
    }
}

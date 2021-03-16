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
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultDashboardController extends AbstractDashboardController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $request = Request::createFromGlobals();

            return $this->redirectToRoute('admin_user_dashboard', [
                // you had to keep this parameter in all your URLs
                'eaContext' => $request->query->get('eaContext'),
            ]);
        }

        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();

        return $this->redirect($routeBuilder->setController(CustomerCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        $title = $this->translator->trans('header.name');
        return Dashboard::new()->setTitle($title);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('dashboard.home', 'fa fa-home');
        yield MenuItem::linkToCrud('dashboard.market', 'fas fa-sitemap', Market::class);
        yield MenuItem::linkToCrud('dashboard.customer', 'fas fa-users', Customer::class);
        yield MenuItem::linkToCrud('dashboard.order', 'fas fa-shopping-cart', CustomerOrder::class);
        yield MenuItem::linkToCrud('dashboard.payment', 'fas fa-credit-card', Payment::class);
        yield MenuItem::linkToCrud('dashboard.type', 'fas fa-check-square', Type::class);
        yield MenuItem::linkToCrud('dashboard.user', 'fas fa-user', User::class);
    }
}

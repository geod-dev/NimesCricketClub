<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Entity\News;
use App\Entity\Partner;
use App\Entity\Player;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
         return $this->render('admin/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('NCC Admin')
            ->disableDarkMode()
            ->setLocales(["fr", "en"])
            ->setFaviconPath("favicon.ico");
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Back to Website', 'fa fa-caret-left', "app_home");
        yield MenuItem::section('Modules');
        yield MenuItem::linkToCrud('News', 'fa fa-newspaper', News::class);
        yield MenuItem::linkToCrud('Players', 'fa fa-baseball-bat-ball', Player::class);
        yield MenuItem::linkToCrud('Partners', 'fa fa-handshake', Partner::class);
        yield MenuItem::linkToCrud('Activities', 'fa fa-bolt-lightning', Activity::class);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('admin/css/admin.css');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Entity\Competition;
use App\Entity\ContactSubmission;
use App\Entity\CustomerForm;
use App\Entity\CustomerFormEntry;
use App\Entity\News;
use App\Entity\NewsletterSubscriber;
use App\Entity\Partner;
use App\Entity\Player;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        yield MenuItem::linkToCrud('Players', 'fa fa-shirt', Player::class);
        yield MenuItem::linkToCrud('Partners', 'fa fa-handshake', Partner::class);
        yield MenuItem::linkToCrud('Activities', 'fa fa-bolt-lightning', Activity::class);
        yield MenuItem::linkToCrud('Competitions', 'fa fa-baseball-bat-ball', Competition::class);
        yield MenuItem::linkToCrud('Subscribers', 'fa fa-envelope-open-text', NewsletterSubscriber::class);
        yield MenuItem::linkToCrud('Contacts', 'fa fa-address-book', ContactSubmission::class);
        yield MenuItem::linkToCrud('Forms', 'fa fa-rectangle-list', CustomerForm::class)->setController(CustomerFormCrudController::class);
        yield MenuItem::linkToCrud('Submissions', 'fa fa-align-left', CustomerFormEntry::class)->setController(CustomerFormEntryCrudController::class);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('admin/css/admin.css')
            ->addJsFile('admin/js/trix-editor-upload.js');
    }
}

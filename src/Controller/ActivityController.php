<?php

namespace App\Controller;

use App\Entity\Activity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController
{
    #[Route('/activites', name: 'app_activity')]
    public function index(): Response
    {
        return $this->render('activity/index.html.twig');
    }

    #[Route('/activite/{slug}', name: 'app_activity_detail')]
    public function activity(Activity $activity): Response
    {
        return $this->render('activity/detail.html.twig', [
            "activity" => $activity
        ]);
    }
}

<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompetitionController extends AbstractController
{
    #[Route('/competitions', name: 'app_competition')]
    public function index(
        CompetitionRepository $competitionRepository,
    ): Response
    {
        return $this->render('competition/index.html.twig', [
            'competitions' => $competitionRepository->findBy([], ["eventDate" => "DESC"]),
        ]);
    }
}

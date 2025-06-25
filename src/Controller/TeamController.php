<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    #[Route('/joueurs', name: 'app_team')]
    public function index(PlayerRepository $repository): Response
    {
        return $this->render('team/index.html.twig', [
            'players' => $repository->findAll(),
        ]);
    }
}

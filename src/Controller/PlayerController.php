<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    #[Route('/joueurs', name: 'app_player')]
    public function index(PlayerRepository $repository): Response
    {
        return $this->render('player/index.html.twig', [
            'players' => $repository->findAll(),
        ]);
    }
}

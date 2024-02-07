<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    #[Route('/actus', name: 'app_news')]
    public function index(NewsRepository $repository): Response
    {
        return $this->render('blog/index.html.twig', [
            'news' => $repository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/actus/{slug}', name: 'app_news_post')]
    public function news(News $post): Response
    {
        return $this->render('blog/post.html.twig', [
            'post' => $post,
        ]);
    }
}

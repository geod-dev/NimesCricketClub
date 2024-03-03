<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route("/sitemap.xml", name: "sitemap", defaults: ["_format" => "xml"])]
    public function index(
        Request            $request,
        NewsRepository     $newsRepository,
        ActivityRepository $activityRepository
    ): Response
    {
        $hostname = $request->getSchemeAndHttpHost();

        $urls = [];

        $urls[] = [
            'loc' => $this->generateUrl('app_home'),
            'priority' => 1
        ];
        $urls[] = [
            'loc' => $this->generateUrl('app_discover'),
            'priority' => 0.9
        ];
        $urls[] = [
            'loc' => $this->generateUrl('app_activity'),
            'priority' => 0.8
        ];
        $urls[] = [
            'loc' => $this->generateUrl('app_news'),
            'priority' => 0.8
        ];
        $urls[] = [
            'loc' => $this->generateUrl('app_about'),
            'priority' => 0.7
        ];
        $urls[] = [
            'loc' => $this->generateUrl('app_legal'),
            'priority' => 0.2
        ];

        foreach ($activityRepository->findAll() as $activity) {
            $urls[] = [
                'loc' => $this->generateUrl('app_activity_detail', ['slug' => $activity->getSlug()]),
                'lastmod' => $activity->getUpdatedAt()->format('Y-m-d'),
                'priority' => 0.6
            ];
        }

        foreach ($newsRepository->findAll() as $news) {
            $urls[] = [
                'loc' => $this->generateUrl('app_news_post', ['slug' => $news->getSlug()]),
                'lastmod' => $news->getUpdatedAt()->format('Y-m-d'),
                'priority' => 0.5
            ];
        }

        $response = new Response(
            $this->renderView('sitemap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname,
            ])
        );

        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }
}

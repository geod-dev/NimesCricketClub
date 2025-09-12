<?php

namespace App\Controller;

use App\Repository\NewsletterSubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class UnioneWebhookController extends AbstractController
{
    #[Route('/unione-webhook', name: 'app_unione_webhook')]
    public function index(
        Request                        $request,
        NewsletterSubscriberRepository $subscriberRepository,
        EntityManagerInterface         $entityManager
    ): Response
    {
        $apiKey = $this->getParameter('api_key');

        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!isset($data['auth'])) throw new BadRequestHttpException('Missing auth');
        $receivedAuth = $data['auth'];

        $data['auth'] = $apiKey;
        $jsonToHash = json_encode($data, JSON_UNESCAPED_SLASHES);
        $computedAuth = md5($jsonToHash);
        if ($computedAuth !== $receivedAuth) throw new AccessDeniedHttpException('Invalid auth');

        // Auth is valid
        $events = $request->request->get('events_by_user')['events'];
        foreach ($events as $event) {
            if ($event['event_name'] !== 'transactional_email_status') continue;
            $eventData = $event['event_data'];
            $email = $eventData['email'];
            $status = $eventData['status'];
            $subscriber = $subscriberRepository->findOneBy(['email' => $email]);
            if (!$subscriber) continue;
            if ($status === 'subscribed') $subscriber->setSubscribed(true);
            if ($status === 'unsubscribed') $subscriber->setSubscribed(false);
        }
        $entityManager->flush();

        return new Response('OK', Response::HTTP_OK);
    }
}

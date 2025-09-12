<?php

namespace App\Controller;

use App\Repository\CustomerFormEntryRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use UnexpectedValueException;

final class StripeWebhookController extends AbstractController
{
    #[Route('/stripe-webhook', name: 'app_stripe_webhook')]
    public function index(
        Request                     $request,
        CustomerFormEntryRepository $formEntryRepository,
        MailService                 $mailService,
        EntityManagerInterface      $entityManager
    ): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $endpointSecret = $this->getParameter('stripe_webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (SignatureVerificationException) {
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        } catch (UnexpectedValueException) {
            return new Response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'checkout.session.completed') {
            $checkoutSession = $event->data->object;
            if ($checkoutSession->status === 'complete' && $checkoutSession->payment_status === 'paid') {
                $entry = $formEntryRepository->findOneBy(['checkoutSessionId' => $checkoutSession->id]);

                $mailService->sendMail([
                    "recipients" => [['email' => $entry->getEmail()]],
                    "body" => [
                        "html" => $this->renderView('emails/payment_confirmation.html.twig', [
                            'entry' => $entry,
                        ]),
                    ],
                    "subject" => "Confirmation de paiement - Nîmes Cricket Club",
                    "from_name" => "Nîmes Cricket Club"
                ]);

                $entry->setIsPaid(true);
                $entityManager->flush();
            }
        }

        return new Response('Webhook handled', Response::HTTP_OK);
    }
}

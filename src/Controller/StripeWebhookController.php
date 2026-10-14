<?php

namespace App\Controller;

use App\Repository\CustomerFormEntryRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\LockMode;
use DateTimeImmutable;
use RuntimeException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use UnexpectedValueException;

final class StripeWebhookController extends AbstractController
{
    #[Route('/stripe-webhook', name: 'app_stripe_webhook', methods: ['POST'])]
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

        if (!$sigHeader) {
            return new Response('Missing signature', Response::HTTP_BAD_REQUEST);
        }

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
                if (!$entry) {
                    return new Response('Checkout session not managed by this application', Response::HTTP_OK);
                }

                // Persist the payment independently of confirmation delivery.
                if (!$entry->isPaid()) {
                    $entry->setIsPaid(true);
                    $entityManager->flush();
                }

                if ($entry->getPaymentConfirmationSentAt() === null) {
                    try {
                        $entityManager->wrapInTransaction(function (EntityManagerInterface $manager) use ($entry, $mailService): void {
                            // Serialize concurrent webhook retries for this entry.
                            $manager->refresh($entry, LockMode::PESSIMISTIC_WRITE);
                            if ($entry->getPaymentConfirmationSentAt() !== null) {
                                return;
                            }

                            $mailService->sendMessage(
                                $entry->getEmail(),
                                'Nîmes Cricket Club',
                                'Confirmation de paiement - Nîmes Cricket Club',
                                $this->renderView('emails/payment_confirmation.html.twig', ['entry' => $entry])
                            );
                            $entry->setPaymentConfirmationSentAt(new DateTimeImmutable());
                        });
                    } catch (RuntimeException) {
                        // Stripe retries the confirmation; the payment is already saved.
                        return new Response('Confirmation delivery failed; retry later', Response::HTTP_SERVICE_UNAVAILABLE);
                    }
                }
            }
        }

        return new Response('Webhook handled', Response::HTTP_OK);
    }
}

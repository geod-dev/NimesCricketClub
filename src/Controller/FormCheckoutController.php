<?php

namespace App\Controller;

use App\Entity\CustomerFormEntry;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FormCheckoutController extends AbstractController
{
    #[Route('/form/checkout/{id}', name: 'app_form_checkout')]
    public function index(
        CustomerFormEntry      $entry,
        StripeService          $stripeService,
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($checkoutSessionId = $entry->getCheckoutSessionId()) {
            $checkoutSession = $stripeService->getCheckoutSession($checkoutSessionId);
            if ($checkoutSession->status !== 'expired') return $this->redirect($checkoutSession->url);
        }
        $checkoutSession = $stripeService->createCheckoutSession($entry);
        $entry->setCheckoutSessionId($checkoutSession->id);
        $entityManager->flush();
        return $this->redirect($checkoutSession->url);
    }

    #[Route('/form/checkout/{id}/callback', name: 'app_form_checkout_callback')]
    public function callback(
        CustomerFormEntry   $entry,
        StripeService       $stripeService,
        TranslatorInterface $translator
    ): Response
    {
        $checkoutSession = $stripeService->getCheckoutSession($entry->getCheckoutSessionId());
        if ($checkoutSession->status === 'complete') {
            return $this->render('form/complete.html.twig', [
                'entry' => $entry,
            ]);
        } elseif ($checkoutSession->status === 'open') {
            return $this->render('form/open.html.twig', [
                'entry' => $entry,
                'completionUrl' => $checkoutSession->url
            ]);
        }
        $this->addFlash('error', $translator->trans('form_checkout.expired.message'));
        return $this->redirectToRoute('app_home');
    }
}

<?php

namespace App\Service;

use App\Entity\CustomerFormEntry;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private StripeClient $stripe;

    public function __construct(
        private readonly string                $stripeSecretKey,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
        $this->stripe = new StripeClient($this->stripeSecretKey);
    }

    public function createCheckoutSession(CustomerFormEntry $entry): Session
    {
        $callbackUrl = $this->urlGenerator->generate('app_form_checkout_callback', [
            'id' => $entry->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        return $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => strval(intval($entry->getPrice() * 100)),
                    'product_data' => [
                        'name' => 'Form',
                        'description' => 'Form',
                    ]
                ],
                'quantity' => 1,
            ]],
            'customer_email' => $entry->getEmail(),
            'mode' => 'payment',
            'success_url' => $callbackUrl,
            'cancel_url' => $callbackUrl
        ]);
    }

    public function getCheckoutSession(string $checkoutSessionId)
    {
        return $this->stripe->checkout->sessions->retrieve($checkoutSessionId);
    }
}

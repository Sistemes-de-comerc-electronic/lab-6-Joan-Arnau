<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class StripeService
{
    private string $secretKey;
    private string $webhookSecret;

    public function __construct(string $secretKey, string $webhookSecret)
    {
        $this->secretKey = $secretKey;
        $this->webhookSecret = $webhookSecret;
        Stripe::setApiKey($this->secretKey);
    }

    public function createCheckoutSession(int $amountCents, string $successUrl, string $cancelUrl, array $metadata = []): Session
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Compra'],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
        ]);
    }

    public function constructWebhookEvent(string $payload, string $sigHeader)
    {
        return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
    }
}

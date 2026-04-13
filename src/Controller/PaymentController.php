<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[Route('/create-checkout-session', name: 'payment_create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        // import fix 20 EUR = 2000 cents
        $successUrl = $this->urlGenerator->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $this->urlGenerator->generate('checkout_page', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?canceled=true';

        $session = $this->stripeService->createCheckoutSession(2000, $successUrl, $cancelUrl);

        return new JsonResponse(['id' => $session->id, 'url' => $session->url]);
    }

    #[Route('/webhook', name: 'payment_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        if (!$sigHeader) {
            return new Response('Missing Stripe-Signature header', 400);
        }

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);
        } catch (\Exception $e) {
            return new Response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $emailAddr = $session->customer_details->email ?? null;
            if ($emailAddr) {
                $email = (new Email())
                    ->from('no-reply@localhost')
                    ->to($emailAddr)
                    ->subject('Gràcies per la compra')
                    ->text('Gràcies per la teva compra.');

                try {
                    $this->mailer->send($email);
                } catch (\Throwable $ex) {
                    // log but do not break webhook
                    error_log('Mailer error: ' . $ex->getMessage());
                }
            }
        }

        return new Response('', 200);
    }

    #[Route('/success', name: 'payment_success', methods: ['GET'])]
    public function success(): Response
    {
        return new Response('<html><body><h1>Pagament confirmat. Rebràs un correu de confirmació aviat.</h1></body></html>');
    }

    #[Route('/checkout', name: 'checkout_page', methods: ['GET'])]
    public function checkoutPage(): Response
    {
        return $this->render('payment/checkout.html.twig', [
            'stripe_publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? ''
        ]);
    }
}

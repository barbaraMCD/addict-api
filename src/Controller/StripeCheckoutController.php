<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Price;
use Stripe\Exception\ApiErrorException;
use Stripe\BillingPortal\Session as BillingSession;

class StripeCheckoutController extends AbstractController
{
    public function __construct(private string $stripeSecretKey)
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    #[Route('/stripe/create-checkout-session', name: 'stripe_create_checkout', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $lookupKey = $data['lookup_key'] ?? null;

        if (!$lookupKey) {
            return new JsonResponse(['error' => 'lookup_key is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $prices = Price::all([
                'lookup_keys' => [$lookupKey],
                'expand' => ['data.product']
            ]);

            if (empty($prices->data)) {
                return new JsonResponse(['error' => 'Price not found'], Response::HTTP_NOT_FOUND);
            }

            $checkoutSession = Session::create([
                'line_items' => [[
                    'price' => $prices->data[0]->id,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $_ENV['FRONTEND_URL'] . '/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $_ENV['FRONTEND_URL'] . '/cancel',
            ]);

            return new JsonResponse([
                'checkout_url' => $checkoutSession->url,
                'session_id' => $checkoutSession->id
            ]);
        } catch (ApiErrorException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/stripe/create-portal-session', name: 'stripe_create_portal', methods: ['POST'])]
    public function createPortalSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sessionId = $data['session_id'] ?? null;

        if (!$sessionId) {
            return new JsonResponse(['error' => 'session_id is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $checkoutSession = Session::retrieve($sessionId);

            $portalSession = BillingSession::create([
                'customer' => $checkoutSession->customer,
                'return_url' => $_ENV['FRONTEND_URL'],
            ]);

            return new JsonResponse([
                'portal_url' => $portalSession->url
            ]);
        } catch (ApiErrorException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

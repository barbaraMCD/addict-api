<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Subscription as StripeSubscription;

class StripeCancelSubController extends AbstractController
{
    #[Route('/stripe/subscription/cancel', name: 'cancel_subscription', methods: ['POST'])]
    public function Stripe(): JsonResponse
    {
        $user = $this->getUser();
        $subscription = $user->getActiveSubscription();

        if (!$subscription) {
            return new JsonResponse(['error' => 'No active subscription'], 404);
        }

        try {
            // Annuler côté Stripe
            $stripeSubscription = StripeSubscription::retrieve($subscription->getStripeSubscriptionId());
            $stripeSubscription->cancel();

            return new JsonResponse(['message' => 'Subscription cancelled successfully']);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}

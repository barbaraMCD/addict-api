<?php

namespace App\Controller\Stripe;

use App\Entity\User;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StripeCancelSubController extends AbstractController
{
    public function __construct(private string $stripeSecretKey)
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }
    #[Route('/stripe/subscription/cancel', name: 'cancel_subscription', methods: ['POST'])]
    public function Stripe(): JsonResponse
    {

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('User must be an instance of User');
        }
        $subscription = $user->getActiveSubscription();

        if (!$subscription) {
            return new JsonResponse(['error' => 'No active subscription'], 404);
        }

        try {
            $stripeSubscription = StripeSubscription::retrieve($subscription->getStripeSubscriptionId());
            $stripeSubscription->cancel();

            return new JsonResponse(['message' => 'Subscription cancelled successfully']);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}

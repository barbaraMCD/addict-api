<?php

namespace App\Controller\Stripe;

use App\Entity\Subscription;
use App\Enum\Subscription\PlanType;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeWebhookController extends AbstractController
{
    public function __construct(
        private string $stripeSecretKey,
        private string $stripeWebhookSecret,
        private EntityManagerInterface $entityManager,
        private SubscriptionRepository $subscriptionRepository,
        private UserRepository $userRepository,
        private LoggerInterface $logger
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    #[Route('/api/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): Response
    {

        // TODO stripe listen --forward-to localhost:8000/api/stripe/webhook
        // TODO stripe trigger customer.subscription.updated
        // TODO add stripe variables in .env

        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        if (!$sigHeader) {
            return new Response('Missing signature', Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $this->stripeWebhookSecret);
        } catch (\Exception $e) {
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleSessionCompleted($event->data->object);
                break;
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;
            default:
                break;
        }
        return new Response('OK', Response::HTTP_OK);
    }

    private function handleSessionCompleted(Session $session): void
    {
        try {
            $this->logger->error("metadata : ". $session->metadata);
            $userId = $session->metadata->user_id;
            $user = $this->userRepository->find($userId);

            $stripeSubscription = StripeSubscription::retrieve($session->subscription);

            $subscription = new Subscription();
            $subscription->setUser($user);
            $subscription->setStripeSubscriptionId($stripeSubscription->id);
            $subscription->setStripeCustomerId($stripeSubscription->customer);
            $subscription->setCurrentPeriodStart(
                new \DateTimeImmutable('@' . $stripeSubscription->items->data[0]->current_period_start)
            );
            $subscription->setCurrentPeriodEnd(
                new \DateTimeImmutable('@' . $stripeSubscription->items->data[0]->current_period_end)
            );

            $planType = $this->determinePlanType($stripeSubscription);
            $subscription->setPlanType($planType);

            $this->entityManager->persist($subscription);
            $this->entityManager->flush();

            $this->logger->error('Subscription created successfully: ' . $subscription->getId());
        } catch (\Exception $e) {
            if ($e instanceof UniqueConstraintViolationException) {
                $this->logger->error('Duplicate subscription, skipping');
                return;
            }
            $this->logger->error('Failed to create subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    // TODO VOIR COMMENT TESTER CETTE FONCTION
    private function handleInvoicePaymentSucceeded(Invoice $stripeInvoice): void
    {
        try {
            $stripeSubscriptionId = $stripeInvoice->lines->data[0]->parent->subscription_item_details->subscription;

            $subscription = $this->subscriptionRepository->findOneBy([
                'stripeSubscriptionId' => $stripeSubscriptionId
            ]);

            if (!$subscription) {
                $this->logger->error('Subscription not found: ' . $stripeSubscriptionId);
                return;
            }

            $stripeSubscription = StripeSubscription::retrieve($stripeSubscriptionId);

            $this->logger->error("subscription id: " . $subscription->getStripeCustomerId());
            $this->logger->error("subscription date start: " . $stripeSubscription->current_period_start);

            $subscription->setCurrentPeriodStart(
                new \DateTimeImmutable('@' . $stripeSubscription->items->data[0]->current_period_start)
            );
            $subscription->setCurrentPeriodEnd(
                new \DateTimeImmutable('@' . $stripeSubscription->items->data[0]->current_period_end)
            );

            $this->entityManager->flush();

            $this->logger->error('Subscription period updated: ' . $subscription->getId());

        } catch (\Exception $e) {
            $this->logger->error('Error handling invoice payment: ' . $e->getMessage());
        }
    }

    private function determinePlanType(StripeSubscription $stripeSubscription): PlanType
    {
        $interval = $stripeSubscription->items->data[0]->price->recurring->interval;
        $this->logger->error("interval : " . $interval);

        return match($interval) {
            'month' => PlanType::MONTHLY,
            'year' => PlanType::ANNUAL,
        };
    }
}

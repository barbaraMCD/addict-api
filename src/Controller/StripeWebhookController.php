<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Enum\Subscription\PlanType;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use App\Entity\User;
use Stripe\Webhook;
use Stripe\Subscription as StripeSubscription;

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

    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): Response
    {

        // TODO stripe listen --forward-to localhost:8000/stripe/webhook
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
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event->data->object);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
                /*case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;
                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event->data->object);
                    break;*/
            default:
                break;
        }

        return new Response('OK', Response::HTTP_OK);
    }

    private function handleSubscriptionCreated(StripeSubscription $stripeSubscription): void
    {
        try {

            $user = $this->findUserByStripeCustomerId($stripeSubscription->customer);

            if (!$user) {
                $this->logger->error('User not found for customer: ' . $stripeSubscription->customer);
                return;
            }

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
            $this->logger->error('Failed to create subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handleSubscriptionUpdated(StripeSubscription $stripeSubscription): void
    {
        try {
            $subscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $stripeSubscription->id
            ]);

            if (!$subscription) {
                $this->logger->error('Subscription not found: ' . $stripeSubscription->id);
                return;
            }

            $subscription->setCurrentPeriodStart(
                \DateTimeImmutable::createFromFormat('U', $stripeSubscription->items->data[0]->current_period_start)
            );
            $subscription->setCurrentPeriodEnd(
                \DateTimeImmutable::createFromFormat('U', $stripeSubscription->items->data[0]->current_period_end)
            );

            $this->entityManager->flush();
            $this->logger->error('Subscription updated successfully: ' . $subscription->getId());
        } catch (\Exception $e) {
            $this->logger->error('Failed to update subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handleSubscriptionDeleted(\Stripe\Subscription $stripeSubscription): void
    {
        $subscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $stripeSubscription->id
        ]);

        if ($subscription) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
        }
    }

    private function handleInvoicePaymentSucceeded(\Stripe\Invoice $stripeInvoice): void
    {
        // Create receipt logic here
        $subscription = $this->subscriptionRepository->findOneBy([
            'stripeSubscriptionId' => $stripeInvoice->subscription
        ]);

        if ($subscription) {
            // Create Receipt entity here
            // $receipt = new Receipt();
            // $receipt->setSubscription($subscription);
            // $receipt->setAmount($stripeInvoice->amount_paid / 100);
            // etc.
        }
    }

    private function findUserByStripeCustomerId(string $stripeCustomerId): ?User
    {
        // TODO : Implement stripe customer id
        return $this->userRepository->findAll()[0] ?? null;
    }

    private function determinePlanType(StripeSubscription $stripeSubscription): PlanType
    {
        $interval = $stripeSubscription->items->data[0]->price->recurring->interval;

        return match($interval) {
            'month' => PlanType::MONTHLY,
            'year' => PlanType::ANNUAL,
        };
    }
}

<?php

namespace App\Tests;

use App\Enum\Subscription\PlanType;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class StripeTest extends BaseApiTestCase
{
    protected string $token;
    private $stripeProcess;
    private SubscriptionRepository $subscriptionRepository;
    private UserRepository $userRepository;

    public const USER_EMAIL = 'user5@test.local';


    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = self::getContainer();
        $this->subscriptionRepository = $container->get(SubscriptionRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->token = $this->loginUser(self::USER_EMAIL);
        $this->startStripeListener();
    }

    protected function tearDown(): void
    {
        // Stop Stripe CLI listener
        $this->stopStripeListener();
        parent::tearDown();
    }

    private function startStripeListener(): void
    {
        $command = 'stripe listen --forward-to localhost:8000/api/stripe/webhook';
        $this->stripeProcess = proc_open($command, [], $pipes, null, null, ['bypass_shell' => true]);
        sleep(2); // Wait for listener to start
    }

    private function stopStripeListener(): void
    {
        if (is_resource($this->stripeProcess)) {
            proc_terminate($this->stripeProcess);
            proc_close($this->stripeProcess);
        }
    }

    public function testCreateCheckoutSession()
    {
        $client = static::createClient();

        // Create checkout session
        $client->request('POST', '/stripe/create-checkout-session', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['lookup_key' => PlanType::MONTHLY])
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('checkout_url', $response);
        $this->assertArrayHasKey('session_id', $response);
    }

    public function testCannotCreateCheckoutSessionSuccess()
    {
        $client = static::createClient();

        $response = $client->request(
            'POST',
            '/stripe/create-checkout-session',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode(['lookup_key' => "wong_plan"])
            ]
        )->toArray(false);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Price not found', $response['error']);
    }

    public function testWebhookStripeAndCreateSubscription()
    {
        $client = static::createClient();

        $this->stripeProcess = proc_open(
            'stripe listen --forward-to localhost:8000/api/stripe/webhook',
            [],
            $pipes
        );
        sleep(3);

        // Create checkout session
        $client->request('POST', '/stripe/create-checkout-session', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['lookup_key' => PlanType::MONTHLY])
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('checkout_url', $response);
        $this->assertArrayHasKey('session_id', $response);

        $subscription = $this->subscriptionRepository->findSubscriptionByUserEmail(self::USER_EMAIL);

        $this->assertNotNull($subscription);
        $this->assertEquals(PlanType::MONTHLY, $subscription->getPlanType());
    }
}

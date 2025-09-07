<?php

namespace App\Tests;

use Symfony\Component\HttpFoundation\Response;

class SubscriptionTest extends BaseApiTestCase
{
    protected string $token;

    protected function setUp(): void
    {
        $this->token = $this->loginUser('user1@test.local');
    }

    public function testRetrieveSubscription(): void
    {
        $user = $this->createUser($this->generateRandomEmail());

        $userIri = $this->getIriFromId("users", $user['id']);

        $Subscription = $this->createSubscription($userIri);

        $SubscriptionIri = $Subscription['@id'];

        $this->request($SubscriptionIri, [], $this->token)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Subscription retrieval should be successful");

        $response = [
            '@id' => $SubscriptionIri,
            'id' => $this->getIdFromObject($Subscription),
            'user' => $userIri,
            "stripeSubscriptionId" => $Subscription["stripeSubscriptionId"],
            "stripeCustomerId" => $Subscription["stripeCustomerId"],
            "planType" => $Subscription["planType"],
            "currentPeriodStart" => $Subscription["currentPeriodStart"],
            "currentPeriodEnd" => $Subscription["currentPeriodEnd"]
        ];

        $this->assertJsonContains($response, true, "Response should match the created Subscription data");
    }

    public function testSearchFilterSubscription(): void
    {
        $user = $this->createUser();
        $userIri = $this->getIriFromId("users", $user['id']);

        $Subscription = $this->createSubscription($userIri);
        $SubscriptionIri = $Subscription['@id'];

        $response = [
            'hydra:member' => [[
                '@id' => $SubscriptionIri,
                'id' => $this->getIdFromObject($Subscription),
                'user' => $userIri,
                'stripeSubscriptionId' => $Subscription["stripeSubscriptionId"],
                'stripeCustomerId' => $Subscription["stripeCustomerId"],
                'planType' => $Subscription["planType"],
                'currentPeriodStart' => $Subscription["currentPeriodStart"],
                'currentPeriodEnd' => $Subscription["currentPeriodEnd"],
                'active' => $Subscription["active"]
            ]]
        ];

        $this->request(TestEnum::ENDPOINT_SUBSCRIPTIONS->value."?user.id=". $user['id'], [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Subscription search filter by user should be successful");
        $this->assertJsonContains($response, true, "Response should match the created Subscription data");

        // Create another subscription with a past end date to test the active filter
        $Subscription = $this->createSubscription($userIri, "2023-08-31 19:48:17+00");

        $this->request(TestEnum::ENDPOINT_SUBSCRIPTIONS->value."?active=true", [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Subscription search filter by active status should be successful");
        $this->assertCount(1, $response['hydra:member'], 'Should return exactly 1 active subscription');

        $this->request(TestEnum::ENDPOINT_SUBSCRIPTIONS->value."?stripeCustomerId=".$Subscription["stripeCustomerId"], [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Subscription search filter by stripeCustomerId should be successful");

        $this->deleteRequest(
            TESTEnum::ENDPOINT_USERS->value.'/'.$user['id'],
            [
            'json' => [
                'userId' => $user['id'],
            ],
        ],
            $this->token
        )->toArray();

        $response = $this->request(TestEnum::ENDPOINT_SUBSCRIPTIONS->value."?activeUsers=true"."&user.id=".$user['id'], [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Subscription search filter by activeUsers should be successful");
        $this->assertEmpty($response['hydra:member'], "There should be no subscriptions because the user was deleted");

    }
}

<?php

namespace App\Tests;

use App\Enum\Addiction\AddictionType;
use App\Enum\Trigger\TriggerType;
use Symfony\Component\HttpFoundation\Response;

class ConsumptionTest extends BaseApiTestCase
{
    protected string $token;

    protected function setUp(): void
    {
        $this->token = $this->loginUser('user1@test.local');
    }
    public function testRetrieveConsumption(): void
    {

        $addiction = $this->createAddiction();
        $addictionIri = $addiction["@id"];

        $consumption = $this->createConsumption($addictionIri);
        $consumptionIri = $consumption['@id'];

        $this->request($consumptionIri, [], $this->token)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Consumption retrieval should be successful");

        $response = [
            '@id' => $consumptionIri,
            'quantity' => $consumption["quantity"],
            'date' => $consumption["date"],
            'addiction' => [
                '@id' => $addictionIri,
                'type' => $addiction["type"],
            ],
            'triggers' => [],
        ];


        $this->assertJsonContains($response, true, "Response should match the created consumption data");
    }

    public function testUpdateConsumption(): void
    {
        $comment = "Une journée sans clope!";

        $Consumption = $this->createConsumption();
        $ConsumptionIri = $Consumption['@id'];

        $ConsumptionRetrieved = $this->patchRequest($ConsumptionIri, [
            'json' => [
                'comment' => $comment
            ],
        ], $this->token);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Consumption update should be successful");
        $this->assertEquals($comment, $ConsumptionRetrieved['comment'], "Comment should be updated correctly");
    }

    public function testDeleteConsumption(): void
    {
        $consumption = $this->createConsumption();
        $this->deleteRequest($consumption['@id'], [], $this->token);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT, "Consumption deletion should be successful");
    }

    public function testAddTriggersConsumption(): void
    {

        $triggerAnxiety = $this->createTrigger();
        $triggerFriends = $this->createTrigger(TriggerType::FRIENDS->value);

        $addiction = $this->createAddiction();
        $addictionIri = $addiction["@id"];

        $this->postRequest(
            TestEnum::ENDPOINT_CONSUMPTIONS->value,
            [
                'json' => [
                    'addiction' => $addictionIri,
                    'triggers' => [
                        $triggerAnxiety['@id'],
                        $triggerFriends['@id']
                    ]
                ],
            ],
            $this->token
        )->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, "Consumption creation with triggers should be successful");

        $response = [
            'addiction' => $addictionIri,
            'triggers' => [
                $triggerAnxiety['@id'],
                $triggerFriends['@id']
            ],
        ];

        $this->assertJsonContains($response, true, "Response should match the created consumption with triggers");
    }

    public function testUpdateConsumptionIfAlreadyExistsToday(): void
    {
        // test for event subscriber

        $userEmail = $this->generateRandomEmail();

        $user = $this->createUser($userEmail);
        $userIri = $this->getIriFromId("users", $user['id']);

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction["@id"];

        $consumption = $this->createConsumption($addictionIri);
        $consumptionIri = $consumption['@id'];

        $this->request($consumptionIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "First consumption retrieval should be successful");

        $consumptionTwo = $this->createConsumption($addictionIri);

        $this->request($consumptionIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Second consumption retrieval should be successful and update the quantity");

        $response = [
            '@id' => $consumptionIri,
            'quantity' => $consumption["quantity"] + $consumptionTwo["quantity"],
            'date' => $consumption["date"],
            'addiction' => [
                '@id' => $addictionIri,
                'type' => $addiction["type"],
            ],
            'triggers' => [],
        ];

        $this->assertJsonContains($response, true, "Response should reflect the updated quantity for today's consumption");

    }

    public function testDontUpdateConsumptionForMultipleUsers(): void
    {

        // test if create two same addictions for two users the same day , the same addiction wasn't updated
        // test for event subscriber

        // When create addiction, generate random user email and create Caffeine addiction
        $addiction = $this->createAddiction();
        $addictionIri = $addiction["@id"];

        $consumption = $this->createConsumption($addictionIri);
        $consumptionIri = $consumption['@id'];

        $this->request($consumptionIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "First consumption retrieval should be successful");

        $addictionTwo = $this->createAddiction();
        $addictionTwoIri = $addictionTwo["@id"];

        $consumptionTwo = $this->createConsumption($addictionTwoIri);
        $consumptionTwoIri = $consumptionTwo['@id'];

        $this->request($consumptionIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Second consumption retrieval should be successful for the second user");

        $response = [
            '@id' => $consumptionIri,
            'quantity' => $consumption["quantity"],
            'date' => $consumption["date"],
            'addiction' => [
                '@id' => $addictionIri,
                'type' => $addiction["type"],
            ],
            'triggers' => [],
        ];

        $this->assertJsonContains($response, "Response should match the first user's consumption data");

        $this->request($consumptionTwoIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Second consumption retrieval should be successful for the second user");

        $response = [
            '@id' => $consumptionTwoIri,
            'quantity' => $consumptionTwo["quantity"],
            'date' => $consumptionTwo["date"],
            'addiction' => [
                '@id' => $addictionTwoIri,
                'type' => $addictionTwo["type"],
            ],
            'triggers' => [],
        ];

        $this->assertJsonContains($response, true, "Response should match the second user's consumption data");
    }

    public function testSearchFilterConsumption(): void
    {
        $user = $this->createUser();
        $userIri = $this->getIriFromId("users", $user['id']);

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction['@id'];

        $consumption = $this->createConsumption($addictionIri);
        $consumptionIri = $consumption['@id'];

        $responseRetrieved = $this->request(TestEnum::ENDPOINT_CONSUMPTIONS->value.'?addiction.user.id='. $user['id'], [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Consumption search by user's addiction should be successful");

        $response = [
            'hydra:member' => [[
                '@id' => $consumptionIri,
                'id' => $this->getIdFromObject($consumption),
            ]]
        ];

        $this->assertJsonContains($response);

        $member = $responseRetrieved['hydra:member'][0];

        $this->assertArrayHasKey('quantity', $member, "Consumption should have a quantity field");
        $this->assertArrayHasKey('addiction', $member, "Consumption should have an addiction field");
        $this->assertArrayHasKey('triggers', $member, "Consumption should have a triggers field");
    }

    public function testDateFilterConsumption(): void
    {
        $addiction = $this->createAddiction(null, AddictionType::CLOTHES->value);
        $addictionIri = $addiction['@id'];
        $addictionId = $this->getIdFromObject($addiction);

        $todayConsumption = $this->createConsumption($addictionIri, 1, new \DateTimeImmutable('today'));
        $this->createConsumption($addictionIri, 1, new \DateTimeImmutable('yesterday'));

        $responseRetrieved = $this->request(TestEnum::ENDPOINT_CONSUMPTIONS->value.'?addiction.id='.$addictionId."&date[after]=" . (new \DateTimeImmutable('today'))->format('Y-m-d'), [], $this->token)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Consumption search by date filter should be successful");
        $this->assertEquals(1, $responseRetrieved['hydra:totalItems'], "There should be exactly one consumption for today");
        $this->assertEquals($todayConsumption["date"], $responseRetrieved['hydra:member'][0]['date'], "The consumption date should match today's date");
    }

}

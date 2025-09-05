<?php

namespace App\Tests;

use App\Enum\AddictionEnumType;
use App\Enum\TriggerEnumType;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\BaseApiTestCase;

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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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


        $this->assertJsonContains($response);
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
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($comment, $ConsumptionRetrieved['comment']);
    }

    public function testDeleteConsumption(): void
    {
        $consumption = $this->createConsumption();
        $this->deleteRequest($consumption['@id'], [], $this->token);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testAddTriggersConsumption(): void
    {

        $triggerAnxiety = $this->createTrigger();
        $triggerFriends = $this->createTrigger(TriggerEnumType::FRIENDS->value);

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

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = [
            'addiction' => $addictionIri,
            'triggers' => [
                $triggerAnxiety['@id'],
                $triggerFriends['@id']
            ],
        ];


        $this->assertJsonContains($response);
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
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $consumptionTwo = $this->createConsumption($addictionIri);

        $this->request($consumptionIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

        $this->assertJsonContains($response);

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
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $addictionTwo = $this->createAddiction();
        $addictionTwoIri = $addictionTwo["@id"];

        $consumptionTwo = $this->createConsumption($addictionTwoIri);
        $consumptionTwoIri = $consumptionTwo['@id'];

        $this->request($consumptionIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

        $this->assertJsonContains($response);

        $this->request($consumptionTwoIri, [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

        $this->assertJsonContains($response);
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
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            'hydra:member' => [[
                '@id' => $consumptionIri,
                'id' => $this->getIdFromObject($consumption),
            ]]
        ];

        $this->assertJsonContains($response);

        $member = $responseRetrieved['hydra:member'][0];

        $this->assertArrayHasKey('quantity', $member);
        $this->assertArrayHasKey('addiction', $member);
        $this->assertArrayHasKey('triggers', $member);
    }

    public function testDateFilterConsumption(): void
    {
        $addiction = $this->createAddiction(null, AddictionEnumType::CLOTHES->value);
        $addictionIri = $addiction['@id'];
        $addictionId = $this->getIdFromObject($addiction);

        $todayConsumption = $this->createConsumption($addictionIri, 1, new \DateTimeImmutable('today'));
        $this->createConsumption($addictionIri, 1, new \DateTimeImmutable('yesterday'));

        $responseRetrieved = $this->request(TestEnum::ENDPOINT_CONSUMPTIONS->value.'?addiction.id='.$addictionId."&date[after]=" . (new \DateTimeImmutable('today'))->format('Y-m-d'), [], $this->token)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(1, $responseRetrieved['hydra:totalItems']);
        $this->assertEquals($todayConsumption["date"], $responseRetrieved['hydra:member'][0]['date']);
    }

}

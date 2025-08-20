<?php

namespace App\Tests;

use App\Enum\TriggerEnumType;
use Symfony\Component\HttpFoundation\Response;

class ConsumptionTest extends BaseApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testRetrieveConsumption(): void
    {

        $addiction = $this->createAddiction();
        $addictionIri = $addiction["@id"];

        $consumption = $this->createConsumption($addictionIri);
        $consumptionIri = $consumption['@id'];

        // Get consumption by id
        $this->request($consumptionIri)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            '@id' => $consumptionIri,
            'id' => $this->getIdFromObject($consumption),
            'quantity' => $consumption["quantity"],
            'date' => $consumption["date"],
            'addiction' => $addictionIri,
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
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($comment, $ConsumptionRetrieved['comment']);
    }

    public function testDeleteConsumption(): void
    {
        $consumption = $this->createConsumption();
        $this->deleteRequest($consumption['@id']);
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

        $user = $this->createUser("bernard@gmail.com");
        $userIri = $user['@id'];

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction["@id"];

        $consumption = $this->createConsumption($addictionIri);
        $consumptionIri = $consumption['@id'];

        $this->request($consumptionIri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $consumptionTwo = $this->createConsumption($addictionIri);

        $this->request($consumptionIri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            '@id' => $consumptionIri,
            'id' => $this->getIdFromObject($consumption),
            'quantity' => $consumption["quantity"] + $consumptionTwo["quantity"],
            'date' => $consumption["date"],
            'addiction' => $addictionIri,
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

        $this->request($consumptionIri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $addictionTwo = $this->createAddiction();
        $addictionTwoIri = $addictionTwo["@id"];

        $consumptionTwo = $this->createConsumption($addictionTwoIri);
        $consumptionTwoIri = $consumptionTwo['@id'];

        $this->request($consumptionIri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            '@id' => $consumptionIri,
            'id' => $this->getIdFromObject($consumption),
            'quantity' => $consumption["quantity"],
            'date' => $consumption["date"],
            'addiction' => $addictionIri,
            'triggers' => [],
        ];

        $this->assertJsonContains($response);

        $this->request($consumptionTwoIri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            '@id' => $consumptionTwoIri,
            'id' => $this->getIdFromObject($consumptionTwo),
            'quantity' => $consumptionTwo["quantity"],
            'date' => $consumptionTwo["date"],
            'addiction' => $addictionTwoIri,
            'triggers' => [],
        ];

        $this->assertJsonContains($response);
    }

    public function testSearchFilterConsumption(): void
    {
        $user = $this->createUser();
        $userId = $this->getIdFromObject($user);
        $userIri = $user['@id'];

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction['@id'];

        $consumption = $this->createConsumption($addictionIri);
        $consumptionIri = $consumption['@id'];

        // Get addiction by id
        $response = $this->request('/consumptions?addiction.user.id='. $userId)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);


        $this->assertEquals($consumption['date'], $response['date']);
        $this->assertStringContainsString('/addictions/', $response['addiction']);
    }

    public function testDateFilterConsumption(): void
    {
        $user = $this->createUser();
        $userIri = $user['@id'];

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction['@id'];

        $todayConsumption = $this->createConsumption($addictionIri, 1, new \DateTimeImmutable('today'));
        $yesterdayConsumption = $this->createConsumption($addictionIri, 1, new \DateTimeImmutable('yesterday'));

        $response = $this->request('/consumptions?date[before]=' . (new \DateTimeImmutable('today'))->format('Y-m-d'))->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCount(1, $response['hydra:member']);
        $this->assertEquals($todayConsumption['id'], $response['hydra:member'][0]['id']);
    }

}

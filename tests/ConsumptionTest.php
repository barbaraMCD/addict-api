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

        // TODO IL PEUT Y AVOIR QU'UNE SEULE CONSUMPTION /ADDICTION/PERS/JOUR SINON ÇA L'UPDATE DONC FAUT QUE JE FASSE UN PREPERSIST

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
}

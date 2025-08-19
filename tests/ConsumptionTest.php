<?php

namespace App\Tests;

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
        $additionIri = $addiction["@id"];

        $consumption = $this->createConsumption($additionIri);
        $consumptionIri = $consumption['@id'];

        // Get consumption by id
        $this->request($consumptionIri)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            '@id' => $consumptionIri,
            'id' => $this->getIdFromObject($consumption),
            'quantity' => $consumption["quantity"],
            'date' => $consumption["date"],
            'addiction' => $additionIri,
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

    // TODO ADD WITH SEVERALS TRIGGERS
}

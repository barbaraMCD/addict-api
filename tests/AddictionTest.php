<?php

namespace App\Tests;

use App\Enum\AddictionEnumType;
use Symfony\Component\HttpFoundation\Response;

class AddictionTest extends BaseApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testRetrieveAddiction(): void
    {
        $user = $this->createUser($this->generateRandomEmail());

        $userIri = $this->getIriFromId("users", $user['id']);

        $addiction = $this->createAddiction($userIri, AddictionEnumType::CIGARETTES->value);

        $addictionIri = $addiction['@id'];

        $responseRetrieved = $this->request($addictionIri, [], $this->token)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            '@id' => $addictionIri,
            'type' => AddictionEnumType::CIGARETTES->value,
            'consumptions' => []
        ];

        $this->assertJsonContains($response);
        $this->assertArrayHasKey("totalAmount", $responseRetrieved);
        $this->assertArrayHasKey("status", $responseRetrieved);
    }

    public function testUpdateAddiction(): void
    {
        $newAmount = 100;

        $addiction = $this->createAddiction();

        $addictionIri = $addiction['@id'];

        $addictionRetrieved = $this->patchRequest($addictionIri, [
            'json' => [
                'totalAmount' => $newAmount
            ],
        ], $this->token);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($addiction['totalAmount'] + $newAmount, $addictionRetrieved['totalAmount']);
    }

    public function testDeleteAddiction(): void
    {
        $addiction = $this->createAddiction();
        $this->deleteRequest($addiction['@id'], [], $this->token);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testSearchFilterAddiction(): void
    {
        $user = $this->createUser();
        $userIri = $this->getIriFromId("users", $user['id']);

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction['@id'];

        $responseRetrieved = $this->request(TestEnum::ENDPOINT_ADDICTIONS->value."?user.id=". $user['id'], [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            'hydra:member' => [[
                '@id' => $addictionIri,
                'id' => $this->getIdFromObject($addiction),
                'user' => $userIri,
            ]]
        ];

        $this->assertJsonContains($response);

        $member = $responseRetrieved['hydra:member'][0];

        $this->assertArrayHasKey('totalAmount', $member);
        $this->assertArrayHasKey('type', $member);
        $this->assertArrayHasKey('consumptions', $member);
    }
}

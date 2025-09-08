<?php

namespace App\Tests;

use App\Enum\Addiction\AddictionType;
use Symfony\Component\HttpFoundation\Response;

class AddictionTest extends BaseApiTestCase
{
    protected string $token;

    protected function setUp(): void
    {
        $this->token = $this->loginUser('user1@test.local');
    }

    public function testRetrieveAddiction(): void
    {
        $user = $this->createUser($this->generateRandomEmail());

        $userIri = $this->getIriFromId("users", $user['id']);

        $addiction = $this->createAddiction($userIri, AddictionType::CIGARETTES->value);

        $addictionIri = $addiction['@id'];

        $responseRetrieved = $this->request($addictionIri, [], $this->token)->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Addiction retrieval should be successful");

        $response = [
            '@id' => $addictionIri,
            'type' => AddictionType::CIGARETTES->value,
            'consumptions' => []
        ];

        $this->assertJsonContains($response, true, "Response should match the created addiction data");
        $this->assertArrayHasKey("totalAmount", $responseRetrieved, "Response should contain totalAmount");
        $this->assertArrayHasKey("status", $responseRetrieved, "Response should contain status");
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
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Addiction update should be successful");
        $this->assertEquals($addiction['totalAmount'] + $newAmount, $addictionRetrieved['totalAmount'], "Total amount should be updated correctly");
    }

    public function testDeleteAddiction(): void
    {
        $addiction = $this->createAddiction();
        $this->deleteRequest($addiction['@id'], [], $this->token);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT, "Addiction deletion should be successful");
    }

    public function testSearchFilterAddiction(): void
    {
        $user = $this->createUser();
        $userIri = $this->getIriFromId("users", $user['id']);

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction['@id'];

        $responseRetrieved = $this->request(TestEnum::ENDPOINT_ADDICTIONS->value."?user.id=". $user['id'], [], $this->token)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Addiction search filter by user should be successful");

        $response = [
            'hydra:member' => [[
                '@id' => $addictionIri,
                'id' => $this->getIdFromObject($addiction),
                'user' => $userIri,
            ]]
        ];

        $this->assertJsonContains($response, true, "Response should match the created addiction data");

        $member = $responseRetrieved['hydra:member'][0];

        $this->assertArrayHasKey('totalAmount', $member, "Addiction should have a totalAmount field");
        $this->assertArrayHasKey('type', $member, "Addiction should have a type field");
        $this->assertArrayHasKey('consumptions', $member, "Addiction should have a consumptions field");
    }
}

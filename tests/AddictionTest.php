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
        $userIri = $user['@id'];

        $addiction = $this->createAddiction($userIri, AddictionEnumType::CIGARETTES->value);
        $addictionIri = $addiction['@id'];

        // Get addiction by id
        $this->request($addictionIri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = [
            '@id' => $addictionIri,
            'id' => $this->getIdFromObject($addiction),
            'type' => AddictionEnumType::CIGARETTES->value,
            'user' => $userIri,
            'consumptions' => []
        ];

        $this->assertJsonContains($response);
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
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($addiction['totalAmount'] + $newAmount, $addictionRetrieved['totalAmount']);
    }

    public function testDeleteAddiction(): void
    {
        $addiction = $this->createAddiction();
        $this->deleteRequest($addiction['@id']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testSearchFilterAddiction(): void
    {
        $user = $this->createUser();
        $userId = $this->getIdFromObject($user);
        $userIri = $user['@id'];

        $addiction = $this->createAddiction($userIri);
        $addictionIri = $addiction['@id'];

        // Get addiction by id
        $responseRetrieved = $this->request(TestEnum::ENDPOINT_ADDICTIONS->value."?user.id=". $userId)->toArray();
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

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

        $addiction = $this->createAddiction(AddictionEnumType::CIGARETTES->name, $userIri);
        $addictionIri = $addiction['@id'];

        // Get addiction by id
        $this->request($addictionIri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);


        $this->assertJsonContains([
            'name' => AddictionEnumType::CIGARETTES->name,
            'user' => $userIri
        ]);
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
}

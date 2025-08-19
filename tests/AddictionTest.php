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
        $container = self::getContainer();
    }

    public function testRetrieveAddiction(): void
    {
        $user = $this->createUser("tedeeffsteszs@gmail.com");
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

    /*    public function testUpdateTeam(): void
        {
            $addiction = $this->createTeam();
            $addictionIri = $addiction['@id'];
            $this->request($addictionIri, [], $this->token);
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
            $addictionRetrieved = $this->patchRequest($addictionIri, [
                'json' => [
                    'name' => 'Team2',
                ],
            ], $this->token);
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
            $this->assertEquals('Team2', $addictionRetrieved['name']);
        }

        public function testDeleteTeam(): void
        {
            $addiction = $this->createTeam();
            $this->deleteRequest($addiction['@id'], [], $this->token);
            $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        }

        public function testAddTeam(): void
        {
            $userResponse = $this->request(TestEnum::ENDPOINT_USERS.'?email='.TestEnum::EMAIL_MANAGER_5, [], $this->token);

            $userId = $userResponse->toArray()['hydra:member'][0]['@id'];
            $userTeamId = $userResponse->toArray()['hydra:member'][0]['teams'][0];

            $addictionRetrieved = $this->createTeam();
            $addictionId = $addictionRetrieved['@id'];

            $response = $this->patchRequest(
                $userId,
                [
                    'json' => [
                        'teams' => [
                            $userTeamId,
                            $addictionId,
                        ]],
                ],
                $this->token
            );
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
            $responseTeams = $response['teams'];
            self::assertEquals(2, count($responseTeams), 'Manager should have now 2 teams');
        }*/
}

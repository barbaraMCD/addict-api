<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Enum\AddictionEnumType;
use App\Enum\TriggerEnumType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DefaultApiTestCase extends ApiTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    //    $container = self::getContainer();
    }

    protected function request(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();
/*        if ($token) {
            $options['auth_bearer'] = $token;
        }*/

        return $client->request(Request::METHOD_GET, $endpoint, $options);
    }

    protected function postRequest(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();

/*        if ($token) {
            $options['auth_bearer'] = $token;
        }*/
        $options['headers'] = [
            'Content-Type' => 'application/ld+json',
        ];

        return $client->request(Request::METHOD_POST, $endpoint, $options);
    }

    protected function putRequest(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();
/*        if ($token) {
            $options['auth_bearer'] = $token;
        }*/
        $options['headers'] = [
            'Content-Type' => 'application/ld+json',
        ];

        return $client->request(Request::METHOD_PUT, $endpoint, $options);
    }

    protected function patchRequest(string $endpoint, array $options = [], string $token = null): array
    {
        $client = self::createClient();
/*        if ($token) {
            $options['auth_bearer'] = $token;
        }*/
        $options['headers'] = [
            'Content-Type' => 'application/merge-patch+json',
        ];

        return $client->request(Request::METHOD_PATCH, $endpoint, $options)->toArray();
    }

    protected function deleteRequest(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();
/*        if ($token) {
            $options['auth_bearer'] = $token;
        }*/
        $options['headers'] = [
            'Content-Type' => 'application/ld+json',
        ];

        return $client->request(Request::METHOD_DELETE, $endpoint, $options);
    }

    protected function createUser(string $email = "john@local"): array
    {
        $userResponse = $this->postRequest(
            TestEnum::ENDPOINT_USERS->value,
            [
                'json' => [
                    'username' => 'JohnDoe',
                    'email' => $email,
                    'password' => 'fhjez76g',
                ],
            ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $userResponse->toArray();
    }

    protected function createAddiction(string $name = AddictionEnumType::CAFFEINE->value, string $userId = null): array
    {
        if (!$userId) {
            $userRetrievedData = $this->createUser();
            $userId = $userRetrievedData['@id'];
        }

        $addictionResponse = $this->postRequest(
            TestEnum::ENDPOINT_ADDICTIONS->value,
            [
                'json' => [
                    'name' => $name,
                    'user' => $userId,
                ],
            ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $addictionResponse->toArray();
    }

    protected function createTrigger(string $type = TriggerEnumType::BOREDOM->value): array
    {

        $addictionResponse = $this->postRequest(
            TestEnum::ENDPOINT_TRIGGERS->value,
            [
                'json' => [
                    'type' => $type
                ],
            ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $addictionResponse->toArray();
    }

    protected function getIdFromObject($object): string
    {
        return substr($object['@id'], strrpos($object['@id'], '/') + 1);
    }
}

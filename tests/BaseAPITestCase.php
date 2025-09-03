<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Enum\AddictionEnumType;
use App\Enum\TriggerEnumType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class BaseApiTestCase extends ApiTestCase
{
    protected string $token;
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->token = $this->loginUser('user1@test.local');
    }

    protected function request(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();
        if ($token) {
            $options['auth_bearer'] = $token;
        }
        return $client->request(Request::METHOD_GET, $endpoint, $options);
    }

    protected function postRequest(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();

        if ($token) {
            $options['auth_bearer'] = $token;
        }
        $options['headers'] = [
            'Content-Type' => 'application/ld+json',
        ];

        return $client->request(Request::METHOD_POST, $endpoint, $options);
    }

    protected function putRequest(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();
        if ($token) {
            $options['auth_bearer'] = $token;
        }
        $options['headers'] = [
            'Content-Type' => 'application/ld+json',
        ];

        return $client->request(Request::METHOD_PUT, $endpoint, $options);
    }

    protected function patchRequest(string $endpoint, array $options = [], string $token = null): array
    {
        $client = self::createClient();
        if ($token) {
            $options['auth_bearer'] = $token;
        }
        $options['headers'] = [
            'Content-Type' => 'application/merge-patch+json',
        ];

        return $client->request(Request::METHOD_PATCH, $endpoint, $options)->toArray();
    }

    protected function deleteRequest(string $endpoint, array $options = [], string $token = null): ResponseInterface
    {
        $client = self::createClient();
        if ($token) {
            $options['auth_bearer'] = $token;
        }
        $options['headers'] = [
            'Content-Type' => 'application/ld+json',
        ];

        return $client->request(Request::METHOD_DELETE, $endpoint, $options);
    }

    protected function loginUser(string $email = null, string $password = "test"): string
    {
        if (!$email) {
            $email = $this->generateRandomEmail();
        }

        $loginResponse = $this->postRequest(
            TestEnum::ENDPOINT_LOGIN->value,
            [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $loginResponse->toArray();
        if (!isset($data['token'])) {
            throw new \RuntimeException('Token not returned in login response.');
        }

        return $data['token'];

    }

    protected function createUser(string $email = null): array
    {

        if (!$email) {
            $email = $this->generateRandomEmail();
        }

        $userResponse = $this->postRequest(
            TestEnum::ENDPOINT_REGISTER->value,
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

    protected function createAddiction(string $userIri = null, string $type = AddictionEnumType::CAFFEINE->value, int $totalAmount = 50): array
    {
        if (!$userIri) {
            $userRetrievedData = $this->createUser();
            $userIri = $this->getIriFromId("users", $userRetrievedData['id']);
        }

        $addictionResponse = $this->postRequest(
            TestEnum::ENDPOINT_ADDICTIONS->value,
            [
                'json' => [
                    'user' => $userIri,
                    'type' => $type,
                    'totalAmount' => $totalAmount
                ],
            ],
            $this->token
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $addictionResponse->toArray();
    }

    protected function createConsumption(string $addictionIri = null, int $quantity = 2, \DateTimeImmutable $dateTime = null): array
    {
        if (!$addictionIri) {
            $addictionRetrievedData = $this->createAddiction();
            $addictionIri = $addictionRetrievedData['@id'];
        }

        if(!$dateTime) {
            $dateTime = new \DateTime();
        }

        $consumptionResponse = $this->postRequest(
            TestEnum::ENDPOINT_CONSUMPTIONS->value,
            [
                'json' => [
                    'addiction' => $addictionIri,
                    'quantity' => $quantity,
                    'date' => $dateTime->format('Y-m-d H:i:s'),
                ],
            ],
            $this->token
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $consumptionResponse->toArray();
    }

    protected function createTrigger(string $type = null): array
    {
        if(!$type) {
            $type = TriggerEnumType::ANXIETY;
        }

        $triggerResponse = $this->postRequest(
            TestEnum::ENDPOINT_TRIGGERS->value,
            [
                'json' => [
                    'type' => $type
                ],
            ],
            $this->token
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $triggerResponse->toArray();
    }

    protected function getIdFromObject($object): string
    {
        return substr($object['@id'], strrpos($object['@id'], '/') + 1);
    }

    protected function getIriFromId($type, $id): string
    {
        return "/".$type."/".$id;
    }

    public function generateRandomEmail(): string
    {
        return 'test' . bin2hex(random_bytes(5)) . '@example.com';
    }
}

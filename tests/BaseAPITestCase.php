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
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        //    $container = self::getContainer();
    }

    /**
     * Reset les séquences auto-increment pour éviter les conflits
     */
    private function resetDatabaseSequences(): void
    {
        try {
            $em = self::getContainer()->get('doctrine')->getManager();
            $connection = $em->getConnection();
            $platform = $connection->getDatabasePlatform();

            // Pour MySQL
            if ($platform->getName() === 'mysql') {
                $tables = ['user', 'addiction', 'consumption', 'trigger']; // Adaptez selon vos tables
                foreach ($tables as $table) {
                    $connection->executeStatement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                }
            }
            // Pour PostgreSQL
            elseif ($platform->getName() === 'postgresql') {
                $sequences = $connection->fetchAllAssociative(
                    "SELECT schemaname, sequencename FROM pg_sequences WHERE schemaname = 'public'"
                );
                foreach ($sequences as $sequence) {
                    $connection->executeStatement("ALTER SEQUENCE {$sequence['sequencename']} RESTART WITH 1");
                }
            }
        } catch (\Exception $e) {
            // Ignore les erreurs de reset - pas critique
        }
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

    protected function createUser(string $email = null): array
    {

        if (!$email) {
            $email = $this->generateRandomEmail();
        }

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

    protected function createAddiction(string $userIri = null, string $type = AddictionEnumType::CAFFEINE->value, int $totalAmount = 50): array
    {
        if (!$userIri) {
            $userRetrievedData = $this->createUser();
            $userIri = $userRetrievedData['@id'];
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
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $triggerResponse->toArray();
    }

    protected function getIdFromObject($object): string
    {
        return substr($object['@id'], strrpos($object['@id'], '/') + 1);
    }

    public function generateRandomEmail(): string
    {
        return 'test' . bin2hex(random_bytes(5)) . '@example.com';
    }
}

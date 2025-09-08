<?php

namespace Tests;

use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Service\AuthHTTPClientService;
use App\Tests\BaseApiTestCase;
use App\Tests\TestEnum;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends BaseApiTestCase
{
    private const PASSWORD_IN_CREATE_USER = 'fhjez76g';
    private UserRepository $userRepository;
    private SubscriptionRepository $subscriptionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = self::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->subscriptionRepository = $container->get(SubscriptionRepository::class);
    }


    public function testRegisterUser(): void
    {
        $email = $this->generateRandomEmail();
        $response = $this->postRequest('/api/register', [
            'json' => [
                'email' => $email,
                'password' => 'securePassword123'
            ],
        ])->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED, "User registration should be successful");
        $this->assertArrayHasKey('id', $response, "Response should contain user ID");
        $this->assertEquals($email, $response['email'], "Registered email should match the input email");
    }

    public function testCannotRegisterUser(): void
    {
        $email = $this->generateRandomEmail();
        $registerResponse = $this->postRequest('/api/register', [
            'json' => [
                'email' => $email,
                'password' => 'securePassword123'
            ],
        ])->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $registerResponse);
        $this->assertEquals($email, $registerResponse['email']);

        $registerResponse2 = $this->postRequest(TestEnum::ENDPOINT_REGISTER->value, [
            'json' => [
                'email' => $email,
                'password' => 'test'
            ],
        ]);

        $data = $registerResponse2->toArray(false);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT, "Registering with an existing email should fail");
        $this->assertEquals("Email déjà utilisé.", $data['detail'], "Error message should indicate email is already in use");
    }

    public function testLoginUser(): void
    {

        $email = $this->generateRandomEmail();
        $this->createUser($email);

        $loginResponse = $this->postRequest(TestEnum::ENDPOINT_LOGIN->value, [
            'json' => [
                'email' => $email,
                'password' => UserTest::PASSWORD_IN_CREATE_USER
            ],
        ])->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Login should be successful with correct credentials");
        $this->assertArrayHasKey('token', $loginResponse, "Login response should contain a token");
        $this->assertArrayHasKey('refresh_token', $loginResponse, "Login response should contain a refresh_token");
    }

    public function testCannotLoginUser(): void
    {

        $email = $this->generateRandomEmail();
        $this->createUser($email);

        $loginResponse = $this->postRequest(TestEnum::ENDPOINT_LOGIN->value, [
            'json' => [
                'email' => $email,
                'password' => "wrongPassword"
            ],
        ]);

        $loginResponse->toArray(false);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, "Login should fail with incorrect password");
    }

    public function testRefreshTokenRoute(): void
    {

        $email = $this->generateRandomEmail();
        $this->createUser($email);

        $loginResponse = $this->postRequest(TestEnum::ENDPOINT_LOGIN->value, [
            'json' => [
                'email' => $email,
                'password' => UserTest::PASSWORD_IN_CREATE_USER
            ],
        ])->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertArrayHasKey('token', $loginResponse);
        $this->assertArrayHasKey('refresh_token', $loginResponse);

        $refreshToken = $loginResponse['refresh_token'];

        $refreshTokenResponse = $this->postRequest(TESTEnum::ENDPOINT_REFRESH_TOKEN->value, [
            'json' => [
                'refresh_token' => $refreshToken
            ],
        ])->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "Refresh token request should be successful");
        $this->assertArrayHasKey('token', $refreshTokenResponse, "Refresh token response should contain a new token");
        $this->assertArrayHasKey('refresh_token', $refreshTokenResponse, "Refresh token response should contain a new refresh_token");
    }

    public function testDeleteUser(): void
    {
        $token = $this->loginUser('user1@test.local');

        $email = $this->generateRandomEmail();
        $user = $this->createUser($email);
        $userId = $user['id'];
        $userIri = $this->getIriFromId("users", $userId);

        $this->createSubscription($userIri);

        $response = $this->deleteRequest(
            TESTEnum::ENDPOINT_USERS->value.'/'.$userId,
            [
            'json' => [
                'userId' => $userId,
            ],
        ],
            $token
        )->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, "User deletion request should be successful");
        $this->assertEquals('User deleted or anonymized successfully', $response['message'], "Response message should confirm deletion or anonymization");

        $updatedUser = $this->userRepository->find($userId);

        $this->assertNotNull($updatedUser, 'User should still exist in database');

        // Check anonymized format
        $this->assertStringStartsWith('deleted-', $updatedUser->getEmail(), "Email should be anonymized");
        $this->assertStringEndsWith('@anonymous.local', $updatedUser->getEmail(), "Email should be anonymized");
        $this->assertStringStartsWith('deleted-', $updatedUser->getUsername(), "Username should be anonymized");
        $this->assertEquals('', $updatedUser->getPassword(), "Password should be cleared");
        $this->assertEmpty($updatedUser->getAddictions(), "Addictions should be removed");

        $subscription = $this->subscriptionRepository->findOneBy(['user' => $updatedUser]);
        $this->assertNotNull($subscription, 'Subscription should still exist');
    }

    public function testCannotDeleteUserBecauseNoAuth(): void
    {

        $email = $this->generateRandomEmail();
        $user = $this->createUser($email);
        $userId = $user['id'];
        $userIri = $this->getIriFromId("users", $userId);

        $this->createSubscription($userIri);

        $this->deleteRequest(
            TESTEnum::ENDPOINT_USERS->value.'/'.$userId,
            [
            'json' => [
                'userId' => $userId,
            ],
        ],
        )->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, "User deletion without auth should fail");

    }
}

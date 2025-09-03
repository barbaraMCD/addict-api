<?php

namespace Tests;

use App\Tests\BaseApiTestCase;
use App\Tests\TestEnum;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends BaseApiTestCase
{
    private const PASSWORD_IN_CREATE_USER = 'fhjez76g';

    public function testRegisterUser(): void
    {
        $email = $this->generateRandomEmail();
        $response = $this->postRequest('/api/register', [
            'json' => [
                'email' => $email,
                'password' => 'securePassword123'
            ],
        ])->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals($email, $response['email']);
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
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertEquals("Email déjà utilisé.", $data['detail']);
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
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertArrayHasKey('token', $loginResponse);
        $this->assertArrayHasKey('refresh_token', $loginResponse);
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
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertArrayHasKey('token', $refreshTokenResponse);
        $this->assertArrayHasKey('refresh_token', $refreshTokenResponse);
    }
}

<?php

namespace App\Tests\Controller;

use App\Tests\Api\Api;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends Api
{
    public function testGetUserProfileUnauthorizedWhenInvalidToken(): void
    {
        $this->client->request('GET', '/api/profile', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer wrongToken',
        ],
 
        );

        $this->assertProblemDetailsResponse(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetUserProfileForbiddenWhenNoToken(): void
    {
        $this->client->request('GET', '/api/profile', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertProblemDetailsResponse(Response::HTTP_FORBIDDEN);
    }

    public function testGetUserProfileSuccess(): void
    {
        $token = $this->getToken();

        $this->client->request('GET', '/api/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonLikeResponse();

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        $this->assertArrayHasKey('email', $data);
        $this->assertArrayNotHasKey('password', $data);
    }
}

<?php

namespace App\Tests\Controller;

use App\Tests\Api\Api;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends Api
{
    public function testAddUserUnauthorized(): void
    {
        $this->client->request('POST', '/api/users', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer wrongToken'],
            json_encode([
                'email' => 'test@email',
                'password' => 'testpassword',
            ])
        );

        $this->assertProblemDetailsResponse(Response::HTTP_UNAUTHORIZED);
    }
    public function testAddUserInvalid(): void
    {
        $token = $this->getToken();
        $this->client->request('POST', '/api/users', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => '@email',
            'password' => 'ww',
        ]));


        $this->assertProblemDetailsResponse(Response::HTTP_UNPROCESSABLE_ENTITY);
        
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        $this->assertTrue(
            isset($data['violations']) || isset($data['errors']) || isset($data['detail']),
            'Expected a validation error payload (violations/errors/detail).'
        );
    }

    public function testAddUserSuccess(): void
    {
        $token = $this->getToken();
        $this->client->request('POST', '/api/users', [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => '111111cxx',
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertJsonLikeResponse();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('id', $data);
        
        $this->deleteUserWithEmail('test@example.com');
    }
}

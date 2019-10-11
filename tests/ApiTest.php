<?php

use Keycloak\Client\Entity\Client;
use Keycloak\Exception\KeycloakException;
use Keycloak\User\Api as UserApi;
use Keycloak\Client\Api as ClientApi;
use Keycloak\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Keycloak\User\Entity\NewUser;

require_once 'TestClient.php';

final class ApiTest extends TestCase
{
    /**
     * @var UserApi
     */
    protected $userApi;

    /**
     * @var ClientApi
     */
    protected $clientApi;

    /**
     * @var NewUser
     */
    protected $user;

    protected function setUp()
    {
        global $client;
        $this->userApi = new UserApi($client);
        $this->clientApi = new ClientApi($client);
        $this->user = new NewUser(
            'php.unit',
            'php',
            'unit',
            'php.unit@example.com'
        );
    }

    public function testUserCreate(): void
    {
        $userId = $this->userApi->create($this->user);
        $this->assertNotEmpty($userId);
    }

    public function testUserDuplicateCreate(): void
    {
        $this->expectException(KeycloakException::class);
        $this->userApi->create($this->user);
    }

    /**
     * Helper function to get the user.
     * Tests should not share state.
     * Therefor it is impossible to persist an ID between tests and this function is needed.
     * @return User|null
     */
    private function getUser(): ?User
    {
        $users = $this->userApi->findAll(['username' => $this->user->username, 'email' => $this->user->email]);
        $this->assertCount(1, $users);
        return array_pop($users);
    }

    public function testUserFind(): void
    {
        $user = $this->getUser();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testUserFindNothing(): void
    {
        $noUser = $this->userApi->find('blipblop');
        $this->assertNull($noUser);
    }

    public function testUserUpdate(): void
    {
        $user = $this->getUser();

        $user->firstName = 'unit';
        $user->lastName = 'php';
        $this->userApi->update($user);

        $updatedUser = $this->userApi->find($user->id);
        $this->assertEquals('unit', $updatedUser->firstName);
        $this->assertEquals('php', $updatedUser->lastName);
    }

    public function testClientFindAll(): void
    {
        $allClients = $this->clientApi->findAll();
        $this->assertNotEmpty($allClients);
    }

    public function testClientFind(): void
    {
        // account is a standard client that should always exist
        $client = $this->clientApi->findByClientId('account');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Client::class, $this->clientApi->find($client->id));
    }

    public function testClientFindNothing(): void
    {
        $this->assertNull($this->clientApi->findByClientId('blipblop'));
        $this->assertNull($this->clientApi->find('blipblop'));
    }

    public function testUserClientRoles(): void
    {
        $user = $this->getUser();
        $client = $this->clientApi->findByClientId('account');
        $clientRoles = $this->userApi->getClientRoles($user->id, $client->id);
        $this->assertNotEmpty($clientRoles);

        $this->expectException(KeycloakException::class);
        $this->userApi->getClientRoles($user->id, 'blipblop');
    }


    public function testUserDelete(): void
    {
        $user = $this->getUser();

        $this->userApi->delete($user->id);
        $deletedUser = $this->userApi->find($user->id);
        $this->assertNull($deletedUser);
    }
}
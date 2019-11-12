<?php

use Keycloak\Client\Api as ClientApi;
use Keycloak\Client\Entity\Client;
use Keycloak\Exception\KeycloakException;
use Keycloak\User\Entity\CompositeRole;
use Keycloak\User\Entity\Role;
use PHPUnit\Framework\TestCase;

require_once 'TestClient.php';

class ClientTest extends TestCase
{
    /**
     * @var ClientApi
     */
    protected $clientApi;

    protected function setUp(): void
    {
        global $client;
        $this->clientApi = new ClientApi($client);
    }

    public function testFindAll(): void
    {
        $allClients = $this->clientApi->findAll();
        $this->assertNotEmpty($allClients);
    }

    public function testFind(): void
    {
        // account is a standard client that should always exist
        $client = $this->clientApi->findByClientId('account');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Client::class, $this->clientApi->find($client->id));
    }

    public function testFindNothing(): void
    {
        $this->assertNull($this->clientApi->findByClientId('blipblop'));
        $this->assertNull($this->clientApi->find('blipblop'));
    }

    public function testGetRoles(): void
    {
        $client = $this->clientApi->findByClientId('realm-management');
        $this->assertInstanceOf(Client::class, $client);
        $clientRoles = $this->clientApi->getRoles($client->id);
        $this->assertNotEmpty($clientRoles);

        $this->expectException(KeycloakException::class);
        $this->clientApi->getRoles('blipblop');
    }

    public function testGetProtocolMappers(): void
    {
        $client = $this->clientApi->findByClientId('realm-management');
        $this->assertNotEmpty($client->protocolMappers);
    }

    public function testGetCompositeRoles(): void
    {
        $compositeRoles = $this->clientApi->getCompositeRoles('07e9ea75-b6f0-40b7-9bd3-b2d591b37e47');
        $this->assertNotEmpty($compositeRoles);
        $this->assertInstanceOf(Role::class, $compositeRoles[0]);
    }

    public function testGetCompositeRolesWithPermissions(): void
    {
        $compositeRoles = $this->clientApi->getCompositeRolesWithPermissions('07e9ea75-b6f0-40b7-9bd3-b2d591b37e47');
        $this->assertNotEmpty($compositeRoles);
        $this->assertInstanceOf(CompositeRole::class, $compositeRoles[0]);
    }

    public function testGetCompositesFromRole(): void
    {
        $compositeRoles = $this->clientApi->getCompositesFromRole('07e9ea75-b6f0-40b7-9bd3-b2d591b37e47', 'manage-account');
        $this->assertNotEmpty($compositeRoles);
        $this->assertInstanceOf(Role::class, $compositeRoles[0]);
    }
}

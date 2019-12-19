<?php

use Keycloak\Client\Api as ClientApi;
use Keycloak\Client\Entity\Client;
use Keycloak\Exception\KeycloakException;
use Keycloak\Client\Entity\Role;
use PHPUnit\Framework\TestCase;

require_once 'TestClient.php';

class ClientRoleTest extends TestCase
{
    /**
     * @var ClientApi
     */
    protected $clientApi;

    /**
     * @var Role
     */
    protected $role;

    /**
     * @var Role
     */
    protected $permission1;

    /**
     * @var Role
     */
    protected $permission2;

    protected function setUp(): void
    {
        global $client;
        $this->clientApi = new ClientApi($client);
        $this->role = new Role(
            'roleId',
            'role_old',
            'description',
            true,
            true
        );
        $this->permission1 = new Role(
            'permissionId',
            'permission1',
            'description',
            false,
            false
        );
        $this->permission2 = new Role(
            'permissionId2',
            'permission2',
            'description',
            false,
            false
        );
    }

    public function testCreateRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_CLIENT_ID']);
        $this->assertInstanceOf(Client::class, $client);
        $roleId = $this->clientApi->createRole($this->role, $client->id);
        $this->assertNotEmpty($roleId);
    }

    public function testUpdateRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_CLIENT_ID']);
        $this->assertInstanceOf(Client::class, $client);
        $this->clientApi->updateRole($this->role, $client->id, 'role');
        $this->role = $this->clientApi->getRole($this->role, $client->id);
        $this->assertEquals('role', $this->role->name);
    }

    public function testCreatePermissions(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_CLIENT_ID']);
        $permission1 = $this->clientApi->createRole($this->permission1, $client->id);
        $this->assertNotEmpty($permission1);
        $permission2 = $this->clientApi->createRole($this->permission2, $client->id);
        $this->assertNotEmpty($permission2);
    }

    public function testAddPermissionsToRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_CLIENT_ID']);
        $this->role->name = 'role';
        $role = $this->clientApi->getRole($this->role, $client->id);
        $permissions = [
            $this->clientApi->getRole($this->permission1, $client->id),
            $this->clientApi->getRole($this->permission2, $client->id)
        ];
        $this->clientApi->addPermissions($role->name, $client->id, $permissions);
        $this->assertEquals('role', $this->role->name);
    }

    public function testDeletePermissions(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_CLIENT_ID']);
        $this->clientApi->deleteRole($this->permission1->name, $client->id);
        $deletedPermission1 = $this->clientApi->getRole($this->permission1, $client->id);
        $this->clientApi->deleteRole($this->permission2->name, $client->id);
        $deletedPermission2 = $this->clientApi->getRole($this->permission2, $client->id);
        $this->assertNull($deletedPermission1);
        $this->assertNull($deletedPermission2);
    }

    public function testDeleteRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_CLIENT_ID']);
        $this->role->name = 'role';
        $role = $this->clientApi->getRole($this->role, $client->id);
        $this->clientApi->deleteRole($role->name, $client->id);
        $deletedRole = $this->clientApi->getRole($role, $client->id);
        $this->assertNull($deletedRole);
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

    public function testGetCompositeRoles(): void
    {
        $compositeRoles = $this->clientApi->getCompositeRoles('07e9ea75-b6f0-40b7-9bd3-b2d591b37e47');
        $this->assertNotEmpty($compositeRoles);
        $this->assertInstanceOf(Role::class, $compositeRoles[0]);
    }

    public function testGetCompositesFromRole(): void
    {
        $compositeRoles = $this->clientApi->getCompositesFromRole('07e9ea75-b6f0-40b7-9bd3-b2d591b37e47', 'manage-account');
        $this->assertNotEmpty($compositeRoles);
        $this->assertInstanceOf(Role::class, $compositeRoles[0]);
    }
}

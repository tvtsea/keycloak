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
    protected $permission;

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
        $this->permission = new Role(
            'permissionId',
            'permission',
            'description',
            false,
            false
        );
    }

    public function testCreateRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_REALM']);
        $this->assertInstanceOf(Client::class, $client);
        $roleId = $this->clientApi->createRole($this->role, $client->id);
        $this->assertNotEmpty($roleId);
    }

    public function testUpdateRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_REALM']);
        $this->assertInstanceOf(Client::class, $client);
        $this->clientApi->updateRole($this->role, $client->id, 'role');
        $this->role = $this->clientApi->getRole($this->role, $client->id);
        $this->assertEquals('role', $this->role->name);
    }

    public function testCreatePermissions(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_REALM']);
        $permission1 = $this->clientApi->createRole(new Role('permission1_id', 'permission1', 'description', false, false), $client->id);
        $this->assertNotEmpty($permission1);
        $permission2 = $this->clientApi->createRole(new Role('permission2_id', 'permission2', 'description', false, false), $client->id);
        $this->assertNotEmpty($permission2);
    }

    public function testAddPermissionsToRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_REALM']);
        $this->role->name = 'role';
        $role = $this->clientApi->getRole($this->role, $client->id);
        $permissions = [
            $this->clientApi->tryFindRole('permission1', $client->id),
            $this->clientApi->tryFindRole('permission2', $client->id)
        ];
        $this->clientApi->addPermissions($role->name, $client->id, $permissions);
        $this->assertEquals('role', $this->role->name);
    }

    public function testDeletePermissions(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_REALM']);
        $this->role->name = 'permission1';
        $role = $this->clientApi->getRole($this->role, $client->id);
        $this->clientApi->deleteRole($role->name, $client->id);
        $deletedPermission1 = $this->clientApi->tryFindRole($role->name, $client->id);

        $this->role->name = 'permission2';
        $role = $this->clientApi->getRole($this->role, $client->id);
        $this->clientApi->deleteRole($role->name, $client->id);
        $deletedPermission2 = $this->clientApi->tryFindRole($role->name, $client->id);
        $this->assertNull($deletedPermission1);
        $this->assertNull($deletedPermission2);
    }

    public function testDeleteRole(): void
    {
        $client = $this->clientApi->findByClientId($_SERVER['KC_REALM']);
        $this->role->name = 'role';
        $role = $this->clientApi->getRole($this->role, $client->id);
        $this->clientApi->deleteRole($role->name, $client->id);
        $deletedRole = $this->clientApi->tryFindRole($role->name, $client->id);
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

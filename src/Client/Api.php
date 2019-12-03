<?php

namespace Keycloak\Client;

use Keycloak\Client\Entity\Client;
use Keycloak\Exception\KeycloakException;
use Keycloak\KeycloakClient;
use Keycloak\User\Entity\CompositeRole;
use Keycloak\User\Entity\Role;
use Keycloak\User\Entity\User;

class Api
{
    /**
     * @var KeycloakClient
     */
    private $client;

    /**
     * Api constructor.
     * @param KeycloakClient $client
     */
    public function __construct(KeycloakClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $id
     * @return Client|null
     * @throws KeycloakException
     */
    public function find(string $id): ?Client
    {
        try {
            return Client::fromJson($this->client
                ->sendRequest('GET', "clients/$id")
                ->getBody()
                ->getContents());
        } catch (KeycloakException $ex) {
            if ($ex->getPrevious()->getCode() !== 404) {
                throw $ex;
            }
        }
        return null;
    }

    /**
     * @return Client[]
     * @throws KeycloakException
     */
    public function findAll(): array
    {
        $json = $this->client
            ->sendRequest('GET', 'clients')
            ->getBody()
            ->getContents();
        return array_map(static function ($clientArr): Client {
            return Client::fromJson($clientArr);
        }, json_decode($json, true));
    }

    /**
     * @param string $clientId
     * @return Client|null
     * @throws KeycloakException
     */
    public function findByClientId(string $clientId): ?Client
    {
        $allClients = $this->findAll();
        foreach ($allClients as $client) {
            if ($client->clientId === $clientId) {
                return $client;
            }
        }
        return null;
    }

    /**
     * @param string $clientId
     * @param string $roleName
     * @return User[]
     * @throws KeycloakException
     */
    public function findUsersByRoleName(string $clientId, string $roleName): array
    {
        $client = $this->findByClientId($clientId);
        if ($client === null) {
            return [];
        }
        
        $users = $this->client
            ->sendRequest('GET', "clients/$client->id/roles/$roleName/users")
            ->getBody()
            ->getContents();

        return array_map(static function ($userArr): User {
            return User::fromJson($userArr);
        }, json_decode($users, true));
    }

    /**
     * @param string $id
     * @return Role[]
     * @throws KeycloakException
     */
    public function getRoles(string $id): array
    {
        $json = $this->client
            ->sendRequest('GET', "clients/$id/roles")
            ->getBody()
            ->getContents();

        return array_map(static function ($roleArr) use ($id): Role {
            $roleArr['clientId'] = $id;
            return Role::fromJson($roleArr);
        }, json_decode($json, true));
    }

    /**
     * @param string $id
     * @return array
     */
    public function getCompositeRoles(string $id): array
    {
        $json = $this->client
            ->sendRequest('GET', "clients/$id/roles")
            ->getBody()
            ->getContents();

        $jsonDecoded = json_decode($json, true);
        if ($jsonDecoded === null) {
            return [];
        }

        $filtered = array_values(array_filter($jsonDecoded, static function ($roleArr): bool {
            return $roleArr['composite'];
        }));

        return array_map(static function ($roleArr) use ($id): Role {
            $roleArr['clientId'] = $id;
            return Role::fromJson($roleArr);
        }, $filtered);
    }

    /**
     * @param string $id
     * @return array
     */
    public function getCompositeRolesWithPermissions(string $id): array
    {
        $json = $this->client
            ->sendRequest('GET', "clients/$id/roles")
            ->getBody()
            ->getContents();

        $jsonDecoded = json_decode($json, true);
        if ($jsonDecoded === null) {
            return [];
        }

        $filtered = array_values(array_filter($jsonDecoded, static function ($roleArr): bool {
            return $roleArr['composite'];
        }));

        return array_map(function ($roleArr) use ($id): CompositeRole {
            $roleArr['clientId'] = $id;
            $roleArr['permissions'] = $this->getCompositesFromRole($id, $roleArr['name']);
            return CompositeRole::fromJson($roleArr);
        }, $filtered);
    }

    /**
     * @param string $id
     * @param string $roleName
     * @return array
     */
    public function getCompositesFromRole(string $id, string $roleName): array
    {
        $json = $this->client
            ->sendRequest('GET', "clients/$id/roles/$roleName/composites")
            ->getBody()
            ->getContents();

        $jsonDecoded = json_decode($json, true);
        if ($jsonDecoded === null) {
            return [];
        }

        return array_map(static function ($roleArr) use ($id): Role {
            $roleArr['clientId'] = $id;
            return Role::fromJson($roleArr);
        }, $jsonDecoded);
    }
}

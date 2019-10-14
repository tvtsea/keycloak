<?php
namespace Keycloak\User;

use Keycloak\Exception\KeycloakException;
use Keycloak\KeycloakClient;
use Keycloak\User\Entity\NewUser;
use Keycloak\User\Entity\Role;
use Keycloak\User\Entity\User;
use Psr\Http\Message\ResponseInterface;

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
     * @return User
     * @throws KeycloakException
     */
    public function find(string $id): ?User
    {
        try {
            return User::fromJson($this->client
                ->sendRequest('GET', "users/$id")
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
     * @param array $query Can be used for more specific list searches.
     * @Link https://www.keycloak.org/docs-api/7.0/rest-api/index.html#_getusers
     * @return User[]
     * @throws KeycloakException
     */
    public function findAll(array $query = []): array
    {
        $params = http_build_query($query);
        $json = $this->client
            ->sendRequest('GET', 'users' . ($params ? "?$params" : ''))
            ->getBody()
            ->getContents();
        return array_map(static function ($userArr): User {
            return User::fromJson($userArr);
        }, json_decode($json, true));
    }

    /**
     * @return int
     * @throws KeycloakException
     */
    public function count(): int
    {
        return (int)$this->client
            ->sendRequest('GET', 'users/count')
            ->getBody()
            ->getContents();
    }

    /**
     * @param NewUser $newUser
     * @return string id of the newly created user
     * @throws KeycloakException
     */
    public function create(NewUser $newUser): string
    {
        $res = $this->client->sendRequest('POST', 'users', $newUser);

        if ($res->getStatusCode() === 201) {
            return $this->extractUIDFromCreateResponse($res);
        }

        $error = json_decode($res->getBody()->getContents(), true) ?? [];
        if (!empty($error['errorMessage']) && $res->getStatusCode() === 409) {
            throw new KeycloakException($error['errorMessage']);
        }
        throw new KeycloakException('Something went wrong while creating user');
    }

    /**
     * @param ResponseInterface $res
     * @return string
     * @throws KeycloakException
     */
    private function extractUIDFromCreateResponse(ResponseInterface $res): string
    {
        $locationHeaders = $res->getHeader('Location');
        $newUserUrl = reset($locationHeaders);
        if ($newUserUrl === false) {
            throw new KeycloakException('Created user but no Location header received');
        }
        $urlParts = array_reverse(explode('/', $newUserUrl));
        return reset($urlParts);
    }

    /**
     * @param User $user
     * @throws KeycloakException
     */
    public function update(User $user): void
    {
        $this->client->sendRequest('PUT', "users/{$user->id}", $user);
    }

    /**
     * @param string $id
     * @throws KeycloakException
     */
    public function delete(string $id): void
    {
        $this->client->sendRequest('DELETE', "users/$id");
    }

    /**
     * @param string $userId
     * @return Role[]
     */
    public function getRoles(string $userId): array
    {
        $roleJson = $this->client
            ->sendRequest('GET', "users/$userId/role-mappings")
            ->getBody()
            ->getContents();
        $roleArr = json_decode($roleJson, true);


        $realmRoles = !empty($roleArr['realmMappings'])
            ? array_map($this->transformRole(null), $roleArr['realmMappings'])
            : [];

        $clientRoles = !empty($roleArr['clientMappings'])
            ? array_reduce($roleArr['clientMappings'], [$this, 'transformClientRoles'], [])
            : [];
        return array_merge($realmRoles, $clientRoles);
    }

    /**
     * @param Role[] $roles
     * @param array $client
     * @return array
     */
    private function transformClientRoles(array $roles, array $client): array
    {
        $clientRoles = array_map($this->transformRole($client['id']), $client['mappings']);
        return array_merge($roles, $clientRoles);
    }

    /**
     * @param string $userId
     * @param string $clientId
     * @return Role[]
     */
    public function getClientRoles(string $userId, string $clientId): array
    {
        $clientRolesJson = $this->client
            ->sendRequest('GET', "users/$userId/role-mappings/clients/$clientId")
            ->getBody()
            ->getContents();

        $clientRolesArr = json_decode($clientRolesJson, true);
        return array_map($this->transformRole($clientId), $clientRolesArr);
    }

    /**
     * @param string|null $clientId
     * @return callable
     */
    private function transformRole(?string $clientId): callable
    {
        return static function(array $role) use ($clientId): Role {
            $role['clientId'] = $clientId;
            return Role::fromJson($role);
        };
    }
}
<?php
namespace Keycloak\User;

use Keycloak\Exception\KeycloakException;
use Keycloak\KeycloakClient;
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
     * @return User
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
     */
    public function count(): int
    {
        return (int)$this->client
            ->sendRequest('GET', 'users/count')
            ->getBody()
            ->getContents();
    }
}
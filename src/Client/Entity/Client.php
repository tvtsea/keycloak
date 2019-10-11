<?php

namespace Keycloak\Client\Entity;

use Keycloak\JsonDeserializable;

class Client implements JsonDeserializable
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * Client constructor.
     * TODO: this obviously isn't everything yet.
     * @param string $id
     * @param string $clientId
     * @param bool $enabled
     */
    public function __construct(
        string $id,
        string $clientId,
        bool $enabled
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->enabled = $enabled;
    }

    /**
     * @param string|array $json
     * @return Client
     */
    public static function fromJson($json): Client
    {
        $arr = is_array($json) ? $json : json_decode($json, true);
        return new self(
            $arr['id'],
            $arr['clientId'],
            $arr['enabled']
        );
    }
}
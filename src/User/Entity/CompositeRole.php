<?php
/**
 * Copyright (C) A&C systems nv - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by A&C systems <web.support@ac-systems.com>
 */

namespace Keycloak\User\Entity;

use JsonSerializable;
use Keycloak\JsonDeserializable;

/**
 * Class CompositeRole
 * @package Keycloak\User\Entity
 */
class CompositeRole implements JsonSerializable, JsonDeserializable
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var bool
     */
    public $isComposite;

    /**
     * @var bool
     */
    public $isClientRole;

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var array|null 
     */
    public $permissions;

    /**
     * Role constructor.
     * @param string $id
     * @param string $name
     * @param string|null $description
     * @param bool $isComposite
     * @param bool $isClientRole
     * @param string|null $clientId
     * @param array|null $permissions
     */
    public function __construct(
        string $id,
        string $name,
        ?string $description,
        bool $isComposite,
        bool $isClientRole,
        string $clientId,
        ?array $permissions
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->isClientRole = $isClientRole;
        $this->clientId = $clientId;
        $this->isComposite = $isComposite;
        $this->permissions = $permissions;
    }

    /**
     * @return CompositeRole
     */
    public function jsonSerialize(): CompositeRole
    {
        return $this;
    }

    /**
     * @param string|array $json
     * @return CompositeRole Should always return an instance of the class that implements this interface.
     */
    public static function fromJson($json): CompositeRole
    {
        $arr = is_array($json) ? $json : json_decode($json, true);
        return new self(
            $arr['id'],
            $arr['name'],
            $arr['description'] ?? null,
            $arr['composite'] ?? false,
            $arr['clientRole'],
            $arr['clientId'],
            $arr['permissions'] ?? null
        );
    }
}

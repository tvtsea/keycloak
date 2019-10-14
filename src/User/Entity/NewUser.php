<?php
namespace Keycloak\User\Entity;

use JsonSerializable;

/**
 * Class NewUser
 * @package Keycloak\User\Entity
 */
class NewUser implements JsonSerializable
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * NewUser constructor.
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param bool $enabled
     */
    public function __construct(
        string $username,
        string $firstName,
        string $lastName,
        string $email,
        bool $enabled = true
    ) {
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->enabled = $enabled;
    }

    /**
     * @return NewUser
     */
    public function jsonSerialize(): NewUser
    {
        return $this;
    }
}
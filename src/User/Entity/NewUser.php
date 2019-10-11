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
     * NewUser constructor.
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     */
    public function __construct(
        string $username,
        string $firstName,
        string $lastName,
        string $email
    ) {
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    /**
     * @return NewUser
     */
    public function jsonSerialize(): NewUser
    {
        return $this;
    }
}
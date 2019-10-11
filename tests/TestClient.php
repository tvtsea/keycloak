<?php

use Keycloak\KeycloakClient;

class TestClient
{
    public static function createClient(): KeycloakClient
    {
        $credentials = json_decode(file_get_contents(__DIR__ . '/credentials.json'), true);
        $client = new KeycloakClient(
            $credentials['clientId'],
            $credentials['clientSecret'],
            $credentials['realm'],
            $credentials['url']
        );

        return $client;
    }
}

$client = TestClient::createClient();

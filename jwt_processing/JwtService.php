<?php

require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JwtService
{
    private string $secret;
    private int $expiry; // у секундах

    public function __construct(int $expiry = 3600) {
        $read = require_once(__DIR__."/../config/jwt_config.php");
        $this->secret = $read["jwt_key"];
        $this->expiry = $expiry;
    }

    public function encodeToken(array $payload): string
    {
        $now = time();
        $tokenPayload = array_merge([
            'iat' => $now,
            'exp' => $now + $this->expiry,
        ], $payload);

        return JWT::encode($tokenPayload, $this->secret, 'HS256');
    }

    public function encodeUser($userId, $roleId): string
    {
        return $this->encodeToken(["user_id" => $userId, "role_id" => $roleId]);
    }

    public function decodeToken(string $jwt): object
    {
        return JWT::decode($jwt, new Key($this->secret, 'HS256'));
    }

    public function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? '';

        if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getPayload(): ?array
    {
        $token = $this->getTokenFromHeader();
        if (!$token) {
            return null;
        }
        try {
            $decoded = $this->decodeToken($token);
            return (array) $decoded;
        } catch (ExpiredException $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token expired']);
            exit;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
    }
}

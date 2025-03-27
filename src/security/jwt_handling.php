<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

const HASH_ALGORITHM = 'HS256';
const TOKEN_EXPIRATION = 7200; // 2 hours in seconds

function generate_jwt(array $payload): string {
    $issuedAt = time();
    $payload = array_merge($payload, [
        'iat' => $issuedAt,
        'exp' => $issuedAt + TOKEN_EXPIRATION,
        'iss' => $_SERVER['HTTP_HOST'] ?? 'your-domain.com'
    ]);
    
    return JWT::encode($payload, $_SERVER['JWT_SECRET'], HASH_ALGORITHM);
}

function decode_jwt(string $token): stdClass {
    try {
        return JWT::decode($token, new Key($_SERVER['JWT_SECRET'], HASH_ALGORITHM));
    } catch (ExpiredException $e) {
        HttpResponse::fromStatus(['error' => 'Token expired'], 401);
    } catch (SignatureInvalidException $e) {
        HttpResponse::fromStatus(['error' => 'Invalid token signature'], 401);
    } catch (Exception $e) {
        HttpResponse::fromStatus(['error' => 'Invalid token'], 401);
    }
}

function authenticate_jwt(): int {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (empty($authHeader))
        HttpResponse::fromStatus(['error' => 'Authorization header required'], 401);
    
    if (!preg_match('/Bearer\s+(?<token>\S+)/i', $authHeader, $matches))
        HttpResponse::fromStatus(['error' => 'Invalid authorization format'], 400);
    
    try {
        $decoded = decode_jwt($matches['token'], $_SERVER['JWT_SECRET']);
        
        // WE SHOULD VERIFY IF THE USER EXISTS

        if (!property_exists($decoded, 'id') || !is_numeric($decoded->id))
            HttpResponse::fromStatus(['error' => 'Invalid token payload'], 401);
        
        return (int)$decoded->id;
    } catch (Exception $e) {
        HttpResponse::fromStatus(['error' => 'Authentication failed'], 500);
    }
}
?>
<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

const HASH_ALGORITHM = 'HS256';
const TOKEN_EXPIRATION_2H = 7200; // 2 hours in seconds
const TOKEN_EXPIRATION_7D = 604800;

function generate_jwt(array $payload, bool $isRefresh): string {
    $issuedAt = time();
    $payload = array_merge($payload, [
        'iat' => $issuedAt,
        'exp' => $issuedAt + ($isRefresh ? TOKEN_EXPIRATION_7D : TOKEN_EXPIRATION_2H),
        'iss' => $_SERVER['HTTP_HOST'] ?? 'taskhell.com'
    ]);
    
    return JWT::encode(
        $payload,
        $isRefresh ? $_SERVER['JWT_SECRET_REFRESH'] : $_SERVER['JWT_SECRET_SESSION'],
        HASH_ALGORITHM
    );
}

function decode_jwt(string $token, bool $isRefresh): stdClass {
    try {
        return JWT::decode(
            $token,
            $isRefresh
                ? new Key($_SERVER['JWT_SECRET_REFRESH'], HASH_ALGORITHM) 
                : new Key($_SERVER['JWT_SECRET_SESSION'], HASH_ALGORITHM));
    } catch (ExpiredException $e) {
        HttpResponse::fromStatus(['error' => 'Token expired'], 401);
    } catch (SignatureInvalidException $e) {
        HttpResponse::fromStatus(['error' => 'Invalid token signature'], 401);
    } catch (Exception $e) {
        HttpResponse::fromStatus(['error' => 'Invalid token'], 401);
    }
}

function authenticate_jwt(Database $db, bool $isRefresh): int {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (empty($authHeader))
        HttpResponse::fromStatus(['error' => 'Authorization header required'], 401);
    
    if (!preg_match('/Bearer\s+(?<token>\S+)/i', $authHeader, $matches))
        HttpResponse::fromStatus(['error' => 'Invalid authorization format'], 400);
    
    try {
        $token = $matches['token'];
        $decoded = decode_jwt($token, $isRefresh);

        if (!property_exists($decoded, 'id') || !is_numeric($decoded -> id))
            HttpResponse::fromStatus(['error' => 'Invalid token payload'], 401);
        
        $user_id = (int)$decoded->id;

        if (!$db -> user_exists($user_id))
            HttpResponse::fromStatus(['error' => 'User doesn\'t exists'], 403);
        
        $db_token = $db -> get_refresh_token($user_id);
        if (
            !is_array($db_token) ||
            !array_key_exists('refresh_token', $db_token) ||
            is_null($db_token['refresh_token'])
        ) HttpResponse::fromStatus(['error' => 'Token invalid'], 403);
        if(!$isRefresh)
            return $user_id;


        $pepperedToken = hash_hmac("sha256", $token, $_SERVER['PEPPER_JWT']);
        if (!password_verify($pepperedToken, $db_token['refresh_token']))
            HttpResponse::fromStatus(['error' => 'Invalid token'], 401);

        return $user_id;
    } catch (Exception $e) {
        throw $e;
    }
}
?>
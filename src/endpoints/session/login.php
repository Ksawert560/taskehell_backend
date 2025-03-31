<?php
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

$usernameErrors = validate('username', $username, array_merge($VALIDATION_RULES['username'], ['required']));
$passwordErrors = validate('password', $password, array_merge($VALIDATION_RULES['password'], ['required']));
$errors = array_merge($usernameErrors, $passwordErrors);

if(!empty($errors)) HttpResponse::fromStatus(['errors' => $errors], 400);

$user = $db->get_user_by_username($username);

if (!$user || !isset($user['password']) || !is_string($user['password'])) 
    HttpResponse::fromStatus(['error' => 'Invalid credentials'], 401);

$pepperedPassword = hash_hmac("sha256", $password, $_SERVER['PEPPER_PASSWORD']);

if (!password_verify($pepperedPassword, $user['password']))
    HttpResponse::fromStatus(['error' => 'Invalid credentials'], 401);
 
$payload = ['id' => $user['id']];
$jwt_session = generate_jwt($payload, false);
$jwt_refresh = generate_jwt($payload, true);

$hashedJwt = hashMessage($jwt_refresh, true);
$db -> update_refresh_token($user['id'], $hashedJwt);

return HttpResponse::fromStatus([
    'message' => 'logged in successfully',
    'session token' => $jwt_session,
    'refresh token' => $jwt_refresh
], 200);
?>
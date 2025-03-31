<?php
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

$usernameErrors = validate('username', $username, array_merge($VALIDATION_RULES['username'], ['required']));
$passwordErrors = validate('password', $password, array_merge($VALIDATION_RULES['password'], ['required']));
$errors = array_merge($usernameErrors, $passwordErrors);

if(!empty($errors)) HttpResponse::fromStatus(['errors' => $errors], 400);

$hashedPassword = hashMessage($password, false);

$userID = $db -> register_user($username, $hashedPassword);

$payload = ['id' => $userID];
$jwt_session = generate_jwt($payload, false);
$jwt_refresh = generate_jwt($payload, true);

$hashedJwt = hashMessage($jwt_refresh, true);
$db -> update_refresh_token($userID, $hashedJwt);

return HttpResponse::fromStatus([
    'message' => 'user created successfully',
    'session token' => $jwt_session,
    'refresh token' => $jwt_refresh
], 201);
?>
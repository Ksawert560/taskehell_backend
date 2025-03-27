<?php
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

$usernameErrors = validate('username', $username, array_merge($VALIDATION_RULES['username'], ['required']));
$passwordErrors = validate('password', $password, array_merge($VALIDATION_RULES['password'], ['required']));
$errors = array_merge($usernameErrors, $passwordErrors);

if(!empty($errors)) HttpResponse::fromStatus(['errors' => $errors], 400);

$hashedPassword = hashPassword($password);

$userID = $db -> register_user($username, $hashedPassword);

$payload = ['id' => $userID];
$jwt = generate_jwt($payload);

return HttpResponse::fromStatus(['token' => $jwt], 201);
?>
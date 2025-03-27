<?php
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? null;
$password = $data['password'] ?? null;
$hashedPassword = null;

if(isset($username)) {
    $errors = validate("username", $username, $VALIDATION_RULES['username']);
    if(!empty($errors)) HttpResponse::fromStatus(['errors' => $errors], 400);
} elseif (isset($password)) {
    $errors = validate("password", $password, $VALIDATION_RULES['password']);
    if(!empty(errors)) HttpResponse::fromStatus(['errors'=> $errors], 400);

    $hashedPassword = hashPassword($password);
}

$db -> update_user($user_id, $username, $hashedPassword);

return HttpResponse::fromStatus(['message' => 'user updated successfully'], 200);
?>
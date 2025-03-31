<?php
$data = json_decode(file_get_contents('php://input'), true);

$list = $data['name'] ?? null;
$errors = validate('name', $list, $VALIDATION_RULES['list']);

if (!empty($errors)) throw HttpResponse::fromStatus(['errors' => $errors], 400);

$list_id = $db -> add_list($user_id, $list);
return HttpResponse::fromStatus(['message' => "list created successfully", 'list id' => $list_id], 201);
?>
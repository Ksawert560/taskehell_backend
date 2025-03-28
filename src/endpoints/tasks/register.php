<?php
$data = json_decode(file_get_contents('php://input'), true);

$task = $data['task'] ?? null;
$list_id = $data['list id'] ?? null;
$date = $data['due'] ?? null;

$taskErrors = validate("task", $task, array_merge($VALIDATION_RULES['task'], ['required']));
$listErrors = validate('id', $list_id, $VALIDATION_RULES['id']);
$errors = array_merge($taskErrors, $listErrors);

if(isset($date)) {
    $dateErrors = validate("due", $date, $VALIDATION_RULES['datetime']);
    $errors = array_merge($taskErrors, $dateErrors);
}

if (!empty($errors)) HttpResponse::fromStatus(['errors' => $errors], 400);

if(!$db -> is_list_owned_by_user($list_id, $user_id))
    HttpResponse::fromStatus(['error' => "List not owned by the user"], 403);

$task_id = $db -> add_task($list_id, $task, $date);

return HttpResponse::fromStatus(['message' => 'Task registered successfully', 'task id' => $task_id], 201);
?>
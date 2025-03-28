<?php
$data = json_decode(file_get_contents('php://input'), true);

$task_id = $data['task id'] ?? null;

$errors = validate('task id', $task_id, $VALIDATION_RULES['id']);

$list_id = $db -> get_list_id_by_task($task_id);

if(!$db -> is_list_owned_by_user($list_id, $user_id))
    HttpResponse::fromStatus(['error' => "list not owned by the user"], 403);

$db -> remove_task($task_id);

return HttpResponse::fromStatus(['message' => 'task removed successfully'], 200);
?>
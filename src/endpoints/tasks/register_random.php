<?php
$data = json_decode(file_get_contents('php://input'), true);

$list_id = $data['list id'] ?? null;

$listErrors = validate('id', $list_id, $VALIDATION_RULES['id']);

if(!empty($listErrors)) HttpResponse::fromStatus(['errors' => $listErrors], 400);

if(!$db -> is_list_owned_by_user($list_id, $user_id))
    HttpResponse::fromStatus(['error' => "List not owned by the user"], 403);

$task = $db -> generate_random_task();

$task_id = $db -> add_task($list_id, $task, null, 1);

return HttpResponse::fromStatus(['message' => 'Task registered successfully', 'task id' => $task_id, 'task' => $task], 201);
?>
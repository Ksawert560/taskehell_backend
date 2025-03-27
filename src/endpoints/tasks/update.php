<?php
$data = json_decode(file_get_contents('php://input'), true);

$finished = $data['finished'] ?? null;
$task = $data['task'] ?? null;
$task_id = $data['task id'] ?? null;

$errors = validate('task id', $task_id, $VALIDATION_RULES['id']);

if(!isset($finished) && !isset($task)) HttpResponse::fromStatus(['error' => 'Either "finished" or "task" is required'], 400);

if(isset($task)) {
    $taskErrors = validate("task", $task, $VALIDATION_RULES['task']);
    $errors = array_merge($errors, $taskErrors);
}

if(isset($finished)) {
    $finishedErrors = validate("finished", $finished, $VALIDATION_RULES['boolean']);
    $errors = array_merge($errors, $finishedErrors);
}

if(!empty($errors)) HttpResponse::fromStatus(['errors'=> $errors], 400);

$list_id = $db -> get_list_id_by_task($task_id);

if(!$db -> is_list_owned_by_user($list_id, $user_id))
    HttpResponse::fromStatus(['error' => "List not owned by the user"], 403);

$db -> update_task($task_id, $task, $finished);

return HttpResponse::fromStatus(['message' => 'Task updated successfully'], 200);
?>
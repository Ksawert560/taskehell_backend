<?php
$list_id = $_GET['list id'] ?? null;
$finished = $_GET['finished'] ?? null;
$random = $_GET['random'] ?? null;
$errors = validate('id', $list_id, $VALIDATION_RULES['id']);

if(isset($finished)) {
    $finishedErrors = validate("finished", $finished, $VALIDATION_RULES['boolean']);
    $errors = array_merge($errors, $finishedErrors);
}

if(isset($random)) {
    $randomErrors = validate("random", $random, $VALIDATION_RULES['boolean']);
    $errors = array_merge($errors, $finishedErrors);
}
    
if(!empty($errors)) HttpResponse::fromStatus(['errors' => $errors], 400);

if(!$db -> is_list_owned_by_user($list_id, $user_id))
    HttpResponse::fromStatus(['error' => "List not owned by the user"], 403);

$tasks = $db -> get_tasks_by_list($list_id, $finished, $random);

return HttpResponse::fromStatus(['message' => $tasks], 200);
?>
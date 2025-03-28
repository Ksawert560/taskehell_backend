<?php
$data = json_decode(file_get_contents('php://input'), true);

$list_id = $data['list id'] ?? null;

$listErrors = validate('id', $list_id, $VALIDATION_RULES['id']);

if(!empty($listErrors)) HttpResponse::fromStatus(['errors' => $listErrors], 400);

if(!$db -> is_list_owned_by_user($list_id, $user_id))
    HttpResponse::fromStatus(['error' => "list not owned by the user"], 403);

$db -> remove_list($list_id);
return HttpResponse::fromStatus(["message" => "list removed successfully"], 200);
?>
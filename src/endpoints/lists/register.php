<?php
$list_id = $db -> add_list($user_id);
return HttpResponse::fromStatus(['message' => "List created successfully", 'list id' => $list_id], 201);
?>
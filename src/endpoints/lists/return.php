<?php
$lists = $db -> get_lists_by_user($user_id);
return HttpResponse::fromStatus(['message' => $lists], 200);
?>
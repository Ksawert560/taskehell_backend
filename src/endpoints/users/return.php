<?php
$username = $db -> get_username($user_id);
return HttpResponse::fromStatus(['username' => $username['username']], 200);
?>
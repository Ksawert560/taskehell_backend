<?php
    $db -> remove_user($user_id);
    return HttpResponse::fromStatus(["msg" => "User removed successfully"], 200);
?>
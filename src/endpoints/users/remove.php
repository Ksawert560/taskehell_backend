<?php
    $db -> remove_user($user_id);
    return HttpResponse::fromStatus(["message" => "user removed successfully"], 200);
?>
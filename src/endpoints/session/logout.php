<?php
    $db -> delete_refresh_token($user_id);
    return HttpResponse::fromStatus(["message" => "session closed successfully"], 200);
?>
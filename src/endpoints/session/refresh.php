<?php

$payload = ['id' => $user_id];
$jwt_session = generate_jwt($payload, false);
$jwt_refresh = generate_jwt($payload, true);

$hashedJwt = hashMessage($jwt_refresh, true);
$db -> update_refresh_token($user_id, $hashedJwt);

return HttpResponse::fromStatus([
    'message' => 'session extended successfully',
    'session token' => $jwt_session,
    'refresh token' => $jwt_refresh
], 200);
?>
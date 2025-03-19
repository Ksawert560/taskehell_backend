<?php
function hashPassword($password, $pepper) {
    $pepperedPassword = hash_hmac("sha256", $password, $pepper);

    return password_hash($pepperedPassword, PASSWORD_ARGON2ID);
}

function verifyPassword($inputPassword, $hashedPassword, $pepper) {
    $pepperedPassword = hash_hmac("sha256", $password, $pepper);

    return password_verify($pepperedPassword, $hashedPassword);
}
?>
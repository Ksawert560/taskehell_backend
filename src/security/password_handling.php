<?php
function hashMessage(string $message, $isToken): string {
    $peppered = hash_hmac(
        'sha256',
        $message,
        ($isToken
            ? $_SERVER['PEPPER_JWT']
            : $_SERVER['PEPPER_PASSWORD'])
            ?? 'basicpepper');
    $options = [
        'memory_cost' => 1<<17, // 128MB
        'time_cost'   => 4,
        'threads'     => 3
    ];
    
    $hashed = password_hash($peppered, PASSWORD_ARGON2ID, $options);
    if ($hashed === false) {
        throw new HttpResponse(500, ['error' => 'Password hashing failed']);
    }
    
    return $hashed;
}

function verifyPassword(string $inputPassword, string $hashedPassword): bool {
    $peppered = hash_hmac('sha256', $inputPassword, $_SERVER['PEPPER']);
    return password_verify($peppered, $hashedPassword);
}
?>
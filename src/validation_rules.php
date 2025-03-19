<?php
$VALIDATION_RULES = [
    'username' => [
        'max:256',
        'min:4',
        'regex:/^[a-zA-Z0-9_\-@!.]+$/',
    ],
    'password' => [
        'max:256',
        'min:8',
        'uppercase',
        'number',
        'special',
    ],
    'task' => [
        'max:512',
        'min:1',
    ],
    'datetime' => [
        'datetime'
    ],
    'boolean' => [
        'boolean'
    ],
];
?>
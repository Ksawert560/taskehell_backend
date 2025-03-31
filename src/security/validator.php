<?php
$VALIDATION_RULES = [
    'username' => [
        'max:256',
        'min:1',
        'regex:/^[a-zA-Z0-9_\-@!.]+$/',
    ],
    'password' => [
        'max:256',
        'min:8',
        'uppercase',
        'number',
        'special',
    ],
    'id' => [
        'id',
        'required'
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

function validate($field, $value, $rules = []){
    $errors = [];

    foreach ($rules as $rule) {
        if (strpos($rule, 'max:') === 0) {
            $max = (int) explode(':', $rule)[1];
            if (!empty($value) && strlen($value) > $max)
                $errors[] = "$field must not exceed $max characters";
        } elseif (strpos($rule, 'min:') === 0) {
            $min = (int) explode(':', $rule)[1];
            if (!empty($value) && strlen($value) < $min)
                $errors[] = "$field must be at least $min characters";
        } elseif (strpos($rule, 'regex:') === 0) {
            $pattern = substr($rule, 6);
            if (!empty($value) && !preg_match($pattern, $value))
                $errors[] = "$field format is invalid.";
        } elseif ($rule === 'uppercase') {
            if (!empty($value) && !preg_match('/[A-Z]/', $value))
                $errors[] = "$field must contain at least one uppercase letter.";
        } elseif ($rule === 'number') {
            if (!empty($value) && !preg_match('/[0-9]/', $value))
                $errors[] = "$field must contain at least one number.";
        } elseif ($rule === 'special') {
            if (!empty($value) && !preg_match('/[\W_]/', $value))
                $errors[] = "$field must contain at least one special character.";
        } elseif ($rule === 'datetime') {
            $d = DateTime::createFromFormat('Y-m-d H:i:s', $value);
            if (!$d || $d->format('Y-m-d H:i:s') !== $value)
                $errors[] = "$field must be in format YYYY-MM-DD HH:MM:SS.";
        } elseif ($rule === 'boolean') {
            if (!is_bool($value) && !in_array($value, [0, 1, "0", "1"], true))
                $errors[] = "$field must be a boolean (0/1).";
        } elseif ($rule === 'required') {
            if (!isset($value) || $value === '')
                $errors[] = "$field is required";
        } elseif ($rule === 'id') {
            if (!ctype_digit((string)$value) || (int)$value < 0) {
                $errors[] = "$field must be a non-negative integer.";
            }
        }
    }

    return $errors;
}
?>
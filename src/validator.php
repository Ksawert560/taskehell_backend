<?php


function validate($field, $value, $rules = []) {
    $errors = [];

    foreach ($rules as $rule) {
        if(strpos($rule, 'max:') === 0) {
            $max = (int) explode(':', $rule)[1];
            if(strlen($value) > $max) {
                $errors[] = "$field must not exceed $max characters";
            }
        } elseif(strpos($rule, 'min:') === 0) {
            $min = (int) explode(':', $rule)[1];
            if(strlen($value) < $min) {
                $errors[] = "$field must be at least $min characters";
            }
        } elseif (strpos($rule, 'regex:') === 0) {
            $pattern = substr($rule, 6);
            if (!preg_match($pattern, $value)) {
                $errors[] = "$field format is invalid.";
            }
        } elseif ($rule === 'uppercase') {
            if (!preg_match('/[A-Z]/', $value)) {
                $errors[] = "$field must contain at least one uppercase letter.";
            }
        } elseif ($rule === 'number') {
            if (!preg_match('/[0-9]/', $value)) {
                $errors[] = "$field must contain at least one number.";
            } 
        } elseif ($rule === 'special') {
            if (!preg_match('/[\W_]/', $value)) {
                $errors[] = "$field must contain at least one special character.";
            }
        } elseif ($rule === 'datetime') {
            $d = DateTime::createFromFormat('Y-m-d H:i:s', $value);
            if (!$d || $d->format('Y-m-d H:i:s') !== $value) {
                $errors[] = "$field must be in format YYYY-MM-DD HH:MM:SS.";
            }
        } elseif ($rule === 'boolean') {
            if (!is_bool($value)) {
                if (!in_array($value, [0, 1, "0", "1", "true", "false"], true)) {
                    $errors[] = "$field must be a boolean (true/false).";
                }
            }
        }
    }

    return $errors;
}
?>
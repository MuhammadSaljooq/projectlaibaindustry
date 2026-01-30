<?php

function validateRequired($value, $fieldName) {
    if (empty($value) && $value !== '0' && $value !== 0) {
        return "$fieldName is required";
    }
    return null;
}

function validateString($value, $fieldName, $minLength = null, $maxLength = null) {
    if (!is_string($value)) {
        return "$fieldName must be a string";
    }
    if ($minLength !== null && strlen($value) < $minLength) {
        return "$fieldName must be at least $minLength characters";
    }
    if ($maxLength !== null && strlen($value) > $maxLength) {
        return "$fieldName must be at most $maxLength characters";
    }
    return null;
}

function validateNumeric($value, $fieldName, $min = null, $max = null) {
    if (!is_numeric($value)) {
        return "$fieldName must be a number";
    }
    $num = floatval($value);
    if ($min !== null && $num < $min) {
        return "$fieldName must be at least $min";
    }
    if ($max !== null && $num > $max) {
        return "$fieldName must be at most $max";
    }
    return null;
}

function validateInteger($value, $fieldName, $min = null, $max = null) {
    if (!is_numeric($value) || intval($value) != $value) {
        return "$fieldName must be an integer";
    }
    $num = intval($value);
    if ($min !== null && $num < $min) {
        return "$fieldName must be at least $min";
    }
    if ($max !== null && $num > $max) {
        return "$fieldName must be at most $max";
    }
    return null;
}

function validateDate($value, $fieldName) {
    $d = DateTime::createFromFormat('Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) {
        return "$fieldName must be a valid date (YYYY-MM-DD)";
    }
    return null;
}

function validateDateTime($value, $fieldName) {
    $d = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $value);
    if (!$d) {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    }
    if (!$d) {
        return "$fieldName must be a valid datetime";
    }
    return null;
}

function validateEmail($value, $fieldName) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return "$fieldName must be a valid email address";
    }
    return null;
}

function validateArray($value, $fieldName) {
    if (!is_array($value)) {
        return "$fieldName must be an array";
    }
    return null;
}

function validateAll($rules, $data) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? null;
        
        foreach ($fieldRules as $rule) {
            $error = null;
            
            if ($rule === 'required') {
                $error = validateRequired($value, $field);
            } elseif (strpos($rule, 'string') === 0) {
                $error = validateString($value, $field);
            } elseif (strpos($rule, 'numeric') === 0) {
                $error = validateNumeric($value, $field);
            } elseif (strpos($rule, 'integer') === 0) {
                $error = validateInteger($value, $field);
            } elseif ($rule === 'date') {
                $error = validateDate($value, $field);
            } elseif ($rule === 'datetime') {
                $error = validateDateTime($value, $field);
            } elseif ($rule === 'email') {
                $error = validateEmail($value, $field);
            } elseif ($rule === 'array') {
                $error = validateArray($value, $field);
            }
            
            if ($error !== null) {
                $errors[$field] = $error;
                break; // Stop at first error for this field
            }
        }
    }
    
    return empty($errors) ? null : $errors;
}

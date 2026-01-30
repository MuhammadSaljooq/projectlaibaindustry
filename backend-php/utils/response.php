<?php

function jsonResponse($data, $statusCode = 200, $message = null) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = ['data' => $data];
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    
    echo json_encode($response);
    exit();
}

function errorResponse($message, $statusCode = 400, $errors = null) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = ['error' => $message];
    
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    
    echo json_encode($response);
    exit();
}

function successResponse($message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = ['message' => $message];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit();
}

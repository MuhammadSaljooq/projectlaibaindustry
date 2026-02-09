<?php
// Router for PHP built-in server: send /api/* to index.php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri === '' || $uri === '/' || preg_match('~^/api(/|$)~', $uri)) {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require __DIR__ . '/index.php';
    return true;
}
return false;

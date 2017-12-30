<?php

define('CRAYON_SERVER_ROOT_DIR', $_SERVER['DOCUMENT_ROOT']);

// Cambiamos a directorio base, este directorio normalmente lo define
// el server.php mediante el parametro "-t".
chdir(CRAYON_SERVER_ROOT_DIR);

// Generamos un path asegurandonos que simpre incie con / para partir de la base.
$parsed_url = parse_url($_SERVER['REQUEST_URI']);
$path = '/' . ltrim($parsed_url['path'], '/');

// Definimos el include path base.
set_include_path(get_include_path() . ':' . __DIR__);

$full_path = CRAYON_SERVER_ROOT_DIR . $path;

if (file_exists($full_path)) {
    if (is_dir($full_path) && substr($path, strlen($path) - 1, 1) !== '/') {
        $path = rtrim($path, '/') . '/index.php';
    }
    if (strpos($path, '.php') === false) {
        return false;
    } else {
        chdir(dirname($full_path));
        require_once $full_path;
    }
} else {
    include_once 'index.php';
}

<?php

/*
 * Bootstrap de pruebas.
 *
 * El entorno de despliegue inyecta variables de entorno reales (APP_ENV=production,
 * DB_DATABASE=<archivo>, etc.) que PHPUnit no puede sobreescribir con <env> cuando ya
 * existen en el sistema operativo. Aquí las forzamos a valores de prueba ANTES de que
 * el framework arranque, garantizando una base de datos en memoria y modo testing.
 */
$overrides = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'true',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'BCRYPT_ROUNDS' => '4',
];

foreach ($overrides as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__.'/../vendor/autoload.php';

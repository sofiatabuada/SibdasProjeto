<?php

// --------------------------------------------------------------------
// Configuração geral da aplicação
// --------------------------------------------------------------------
define('APP_NAME',      'MediTrack');
define('APP_VERSION',   '1.0.0');
define('BASE_URL',      '');

// --------------------------------------------------------------------
// Configuração da base de dados
// Usa variáveis de ambiente do Railway se disponíveis
// --------------------------------------------------------------------
$mysql_host = getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: 'nozomi.proxy.rlwy.net';
$mysql_port = getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: '41324';
$mysql_db   = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'meditrack';
$mysql_user = getenv('MYSQL_USER') ?: getenv('MYSQLUSER') ?: 'root';
$mysql_pass = getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: 'edteMKmFpTbhtgXdVbtjzEzJJyrJSnxX';

define('MYSQL_HOST',     $mysql_host);
define('MYSQL_PORT',     $mysql_port);
define('MYSQL_DATABASE', $mysql_db);
define('MYSQL_USERNAME', $mysql_user);
define('MYSQL_PASSWORD', $mysql_pass);

// --------------------------------------------------------------------
// Segurança — Encriptação com OpenSSL
// --------------------------------------------------------------------
define('OPENSSL_METHOD', 'AES-256-CBC');
define('OPENSSL_KEY',    'H0SDRQzIGqclX2kbYBk9xspdn9U5f3Wa');
define('OPENSSL_IV',     'BzKAbjuREsHgnw56');
define('MYSQL_AES_KEY',  'Vduu47qL51hLn6bkYkY6NlO1nivsmdfD');

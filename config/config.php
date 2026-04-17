<?php

// --------------------------------------------------------------------
// Configuração geral da aplicação
// --------------------------------------------------------------------
define('APP_NAME',      'MediTrack');
define('APP_VERSION',   '1.0.0');
define('BASE_URL',      '');

// --------------------------------------------------------------------
// Configuração da base de dados
// --------------------------------------------------------------------
define('MYSQL_HOST',     getenv('MYSQL_HOST')     ?: 'nozomi.proxy.rlwy.net');
define('MYSQL_DATABASE', 'meditrack');
define('MYSQL_USERNAME', getenv('MYSQL_USER')     ?: getenv('MYSQLUSER')     ?: 'root');
define('MYSQL_PASSWORD', getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: 'edteMKmFpTbhtgXdVbtjzEzJJyrJSnxX');
define('MYSQL_PORT',     getenv('MYSQL_PORT')     ?: '41324');

// --------------------------------------------------------------------
// Segurança — Encriptação com OpenSSL
// --------------------------------------------------------------------
define('OPENSSL_METHOD', 'AES-256-CBC');
define('OPENSSL_KEY',    'H0SDRQzIGqclX2kbYBk9xspdn9U5f3Wa'); // 32 caracteres
define('OPENSSL_IV',     'BzKAbjuREsHgnw56');                  // 16 caracteres

// Chave para AES_ENCRYPT/AES_DECRYPT no MySQL
define('MYSQL_AES_KEY',  'Vduu47qL51hLn6bkYkY6NlO1nivsmdfD');

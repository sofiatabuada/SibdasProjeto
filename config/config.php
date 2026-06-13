<?php
define('APP_NAME',      'MediTrack');
define('APP_VERSION',   '1.0.0');
// Caminho base da aplicação no URL, calculado dinamicamente a partir do
// caminho do script. Funciona em qualquer pasta de instalação, por exemplo
// /MediTrack (MAMP local) ou /sibdas/1221408/meditrack (servidor de avaliação),
// sem necessidade de alterar este ficheiro.
$mt_script = $_SERVER['SCRIPT_NAME'] ?? '';
define('BASE_URL', preg_replace('#/(public|private)(/.*)?$#', '', $mt_script));

// Caminho base da aplicação no disco (raiz do projeto). Usado para ler
// ficheiros locais (ex.: filemtime), independente da pasta de instalação.
define('BASE_PATH', dirname(__DIR__));

// ---------------------------------------------------------------------------
// Ligação à base de dados
// Mudar DB_ENV para 'isep' ANTES de submeter (servidor das aulas, acessível
// na rede do ISEP) ou deixar 'local' para desenvolver no MAMP.
// ---------------------------------------------------------------------------
define('DB_ENV', 'isep'); // 'local' (MAMP)  |  'isep' (servidor das aulas)

if (DB_ENV === 'isep') {
    define('MYSQL_HOST',     'vsgate-s1.dei.isep.ipp.pt');
    define('MYSQL_PORT',     '10464');
    define('MYSQL_DATABASE', 'db1221408');
    define('MYSQL_USERNAME', '1221408');
    define('MYSQL_PASSWORD', 'tabuada_408');
} else {
    // Desenvolvimento local no MAMP (Mac): utilizador 'root' / password 'root'.
    define('MYSQL_HOST',     'localhost');
    define('MYSQL_PORT',     '');          // MAMP liga por socket: deixar vazio
    define('MYSQL_DATABASE', 'meditrack');
    define('MYSQL_USERNAME', 'root');
    define('MYSQL_PASSWORD', 'root');
}

// DSN único reutilizado em toda a aplicação (a porta só entra se definida).
define(
    'MYSQL_DSN',
    'mysql:host=' . MYSQL_HOST
        . (MYSQL_PORT !== '' ? ';port=' . MYSQL_PORT : '')
        . ';dbname=' . MYSQL_DATABASE
        . ';charset=utf8mb4'
);

define('OPENSSL_METHOD', 'AES-256-CBC');
define('OPENSSL_KEY',    'H0SDRQzIGqclX2kbYBk9xspdn9U5f3Wa');
define('OPENSSL_IV',     'BzKAbjuREsHgnw56');
define('MYSQL_AES_KEY',  'Vduu47qL51hLn6bkYkY6NlO1nivsmdfD');

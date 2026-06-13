<?php
// Ponto de entrada da aplicação.
// Encaminha o visitante para o front office (área pública), seja qual for a
// pasta onde o projeto esteja instalado (ex.: /MediTrack ou
// /sibdas/1221408/meditrack). Usa o caminho do próprio script, por isso não
// depende de configuração.
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: ' . $base . '/public/index.php');
exit;

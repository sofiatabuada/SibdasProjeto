<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();
header('Location: home.php');
exit;

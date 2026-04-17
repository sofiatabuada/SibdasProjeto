<?php
require_once '../config/config.php';

echo "<h2>Teste de Ligação à BD</h2>";
echo "<p>HOST: " . MYSQL_HOST . "</p>";
echo "<p>PORT: " . MYSQL_PORT . "</p>";
echo "<p>DB: " . MYSQL_DATABASE . "</p>";
echo "<p>USER: " . MYSQL_USERNAME . "</p>";

try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color:green'><strong>✅ Ligação bem sucedida!</strong></p>";
} catch (PDOException $e) {
    echo "<p style='color:red'><strong>❌ Erro: " . $e->getMessage() . "</strong></p>";
}

<?php
// Conectar ao banco de dados
$host = '###';  // Endereço IP do servidor MySQL
$dbname = '###';    // Nome do banco de dados
$username = '###'; // Nome de usuário do banco de dados
$password = QFvPzd64zeY'###s'; // Senha do banco de dados
$port = 3306; // Porta do MySQL

try {
    // Conecta ao banco de dados, incluindo a porta
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
}

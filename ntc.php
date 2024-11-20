<?php

// Defina $shortLink como uma string vazia no início
$shortLink = '';

include_once 'conexao.php';

// Verificar se há um código curto na URL
if (isset($_GET['code'])) {
    $shortCode = $_GET['code'];

    // Consultar o banco de dados para encontrar o codigo_contrato, a data de criação e o contador de cliques
    $stmt = $pdo->prepare("SELECT codigo_contrato, created_at, click_count FROM short_links WHERE short_code = :code LIMIT 1");
    $stmt->execute(['code' => $shortCode]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($link) {
        // Verificar se o link foi criado há mais de 24 horas
        $createdAt = new DateTime($link['created_at']);
        $now = new DateTime();
        $interval = $now->diff($createdAt);

        // Verificar se o contador de cliques é menor que 10
        if ($link['click_count'] < 10) {
            if ($interval->h < 24 && $interval->days == 0) {
                // Incrementar o contador de cliques
                $stmt = $pdo->prepare("UPDATE short_links SET click_count = click_count + 1 WHERE short_code = :code");
                $stmt->execute(['code' => $shortCode]);

                // Redirecionar para a URL real com o codigo_contrato
                header("Location: index.php?codigo_contrato=" . $link['codigo_contrato']);
                exit;
            } else {
                echo "<div style='color: red; text-align: center; font-weight: bold; margin-top: 20px;'>Este link expirou!</div>";
                exit;
            }
        } else {
            echo "<div style='color: red; text-align: center; font-weight: bold; margin-top: 20px;'>Este link não está mais disponível.</div>";
            exit;
        }
    } else {
        echo "<div style='color: red; text-align: center; font-weight: bold; margin-top: 20px;'>Link não encontrado!</div>";
        exit;
    }
}

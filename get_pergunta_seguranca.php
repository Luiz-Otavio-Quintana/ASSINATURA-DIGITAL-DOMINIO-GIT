<?php
$hora = 3600; // 1 hora em segundos
ini_set('session.gc_maxlifetime', $hora);

// Inicia a sessão
session_start();
if (!empty($_SESSION['id'])) {

    include_once 'conexao.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $usuario = $_POST['usuario'];
        $query = "SELECT pergunta FROM DBA_USERS WHERE usuario='$usuario' LIMIT 1";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);

        echo $user['pergunta'];
    }
} else {
    $_SESSION['msg'] = "Área restrita";
    header("Location: main.php");
}

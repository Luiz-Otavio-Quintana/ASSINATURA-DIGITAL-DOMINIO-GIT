<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define o tempo de vida da sessão em segundos (por exemplo, 1 hora)
$hora = 3600; // 1 hora em segundos
ini_set('session.gc_maxlifetime', $hora);

// Inicia a sessão
session_start();
include_once("conexao.php"); // Certifique-se de que este arquivo usa PDO para a conexão

$btnLogin = filter_input(INPUT_POST, 'btnLogin', FILTER_SANITIZE_SPECIAL_CHARS);
if ($btnLogin) {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($usuario) && !empty($senha)) {
        // Pesquisar o usuário no BD
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, nivel, usuario FROM DBA_USERS WHERE usuario = :usuario LIMIT 1");
        $stmt->execute(['usuario' => $usuario]);

        // Verifica se o usuário foi encontrado
        if ($row_usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Verifica a senha
            if (password_verify($senha, $row_usuario['senha'])) {
                // Configura as variáveis de sessão
                $_SESSION['id'] = $row_usuario['id'];
                $_SESSION['nome'] = $row_usuario['nome'];
                $_SESSION['email'] = $row_usuario['email'];
                $_SESSION['usuario'] = $row_usuario['usuario'];
                $_SESSION['nivel'] = $row_usuario['nivel'];

                // Redireciona para a página inicial
                header("Location: encurtador.php");
                exit; // Certifique-se de sair após o redirecionamento
            } else {
                $_SESSION['msg'] = "Login e senha incorretos!";
                header("Location: main.php");
                exit;
            }
        } else {
            $_SESSION['msg'] = "Login e senha incorretos!";
            header("Location: main.php");
            exit;
        }
    } else {
        $_SESSION['msg'] = "Login e senha não podem ser vazios!";
        header("Location: main.php");
        exit;
    }
} else {
    $_SESSION['msg'] = "Página não encontrada";
    header("Location: main.php");
    exit;
}

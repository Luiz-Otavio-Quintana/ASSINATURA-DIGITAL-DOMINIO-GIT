<?php
// Define o tempo de vida da sessão em segundos (por exemplo, 1 hora)
$hora = 3600; // 1 hora em segundos
ini_set('session.gc_maxlifetime', $hora);

// Inicia a sessão
session_start(); // É obrigatório iniciar a sessão antes de manipulá-la

var_dump($_SESSION);
// Verifica se o usuário está autenticado
if (!empty($_SESSION['id'])) {
    header("Location: encurtador.php");
    exit; // Garante que o script pare após o redirecionamento
}
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/html.css">
    <title>Encurtador Netcol</title>
</head>

<body>

    <div id="centralizar">
        <div id="login">
            <div id="logo"><img src="img/logonovo.png" alt="Netcol" srcset=""></div>
            <h4>Encurtador Netcol</h4>
            <?php
            if (isset($_SESSION['msg'])) {
                echo $_SESSION['msg'];
                unset($_SESSION['msg']);
            }
            if (isset($_SESSION['msgcad'])) {
                echo $_SESSION['msgcad'];
                unset($_SESSION['msgcad']);
            }
            ?>

            <form method="POST" action="valida.php">

                <input type="text" name="usuario" placeholder="Usuário" required><br><br>

                <input type="password" name="senha" placeholder="Senha" required><br><br>

                <input type="submit" class="btn btn-success" name="btnLogin" value="Acessar">


            </form>
            <br><br>

            <div class="form-group">
                <a href="recuperar_senha.html" class="btn btn-secondary">Esqueci minha senha</a>
            </div>
        </div>
    </div>
</body>

</html>



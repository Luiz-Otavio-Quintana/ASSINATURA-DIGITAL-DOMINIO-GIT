<?php
include_once 'conexao.php';
// Iniciar a variável de mensagem e redirecionamento
$message = "";
$redirect = "recuperar_senha.html";

// Iniciar a variável de mensagem e redirecionamento
$message = "";
$redirect = "recuperar_senha.html";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitização das entradas
    $usuario = htmlspecialchars(trim($_POST['usuario']));
    $resposta_seguranca = htmlspecialchars(trim($_POST['resposta_seguranca']));
    $nova_senha = password_hash(htmlspecialchars(trim($_POST['nova_senha'])), PASSWORD_DEFAULT);

    // Consulta o usuário e verifica a resposta de segurança
    $query = "SELECT resposta FROM DBA_USERS WHERE usuario=:usuario LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $resposta_seguranca === $user['resposta']) {
        // Atualiza a senha
        $update_query = "UPDATE DBA_USERS SET senha=:nova_senha WHERE usuario=:usuario";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':nova_senha', $nova_senha);
        $update_stmt->bindParam(':usuario', $usuario);

        if ($update_stmt->execute()) {
            $message = "Senha alterada com sucesso";
            $redirect = "main.php";
        } else {
            $message = "Erro ao alterar a senha";
        }
    } else {
        $message = "Resposta de segurança incorreta";
    }
} else {
    $message = "Método de solicitação inválido";
}

// Exibir a mensagem de feedback e redirecionar
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecionamento</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }

        .alert {
            font-size: 18px;
        }

        .logo {
            display: block;
            margin: 20px auto;
            width: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="img/logonovo.png" alt="Logo da Empresa" class="logo">
        <div class="alert alert-info">
            <?php echo $message; ?>
            <br>
            Você será redirecionado em <span id="counter">5</span> segundos.
        </div>
    </div>

    <script>
        function countdown() {
            var counter = document.getElementById('counter');
            var count = parseInt(counter.innerText);

            var interval = setInterval(function() {
                count--;
                counter.innerText = count;

                if (count === 0) {
                    clearInterval(interval);
                    window.location.href = '<?php echo $redirect; ?>';
                }
            }, 1000);
        }

        countdown();
    </script>
</body>

</html>
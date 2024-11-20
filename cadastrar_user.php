<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//var_dump($_SESSION);

$hora = 3600; // 1 hora em segundos
ini_set('session.gc_maxlifetime', $hora);

// Inicia a sessão
session_start();
//var_dump($_SESSION);

if (!empty($_SESSION['id'])) {


    include_once 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

    if (isset($_POST['btnCadUsuario'])) {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $nivel = filter_input(INPUT_POST, 'nivel', FILTER_SANITIZE_NUMBER_INT);
        $pergunta = filter_input(INPUT_POST, 'pergunta', FILTER_SANITIZE_SPECIAL_CHARS);
        $resposta = filter_input(INPUT_POST, 'resposta', FILTER_SANITIZE_SPECIAL_CHARS);
        $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $query = "INSERT INTO DBA_USERS (nome, email, usuario, senha, nivel, pergunta, resposta) VALUES (:nome, :email, :usuario, :senha, :nivel, :pergunta, :resposta)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':nivel', $nivel);
        $stmt->bindParam(':pergunta', $pergunta);
        $stmt->bindParam(':resposta', $resposta);

        if ($stmt->execute()) {
            header("Location: cadastrar_user.php");
            exit();
        } else {
            $_SESSION['msg'] = "Erro ao cadastrar o usuário";
        }
    }

    // Consulta para listar todos os usuários
    $queryUsuarios = "SELECT nome, email, usuario, nivel FROM DBA_USERS";
    $stmtUsuarios = $pdo->query($queryUsuarios);
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php

    if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 01) {
        echo "Não autorizado";
        exit;
    }
    ?>



    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastrar Usuário</title>
        <link rel="stylesheet" href="css/cadastrar_user.css">
        <link rel="stylesheet" href="css/html.css">
        <link rel="stylesheet" href="css/fonts.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">
    </head>

    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="text-center mb-4">Cadastrar Novo Usuário</h2>

                            <div class="btn2_div">
                                <button class="btn2" onclick="window.location.href='encurtador.php'">Inicio</button>
                                <button class="btn2_v" onclick="window.location.href='sair.php'">Sair</button>

                            </div>
                            <?php
                            if (isset($_SESSION['msg'])) {
                                echo '<div class="alert alert-danger">' . $_SESSION['msg'] . '</div>';
                                unset($_SESSION['msg']);
                            }
                            ?>

                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="nome">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o nome completo" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Digite o seu e-mail" required>
                                </div>
                                <div class="form-group">
                                    <label for="nivel">Permissão</label>
                                    <select class="form-control" id="nivel" name="nivel" required>
                                        <option value="">Selecione a permissão</option>
                                        <option value="01">ADM</option>
                                        <option value="02">NOC</option>
                                        <option value="03">ATENDIMENTO</option>
                                        <option value="04">VISUALIZAÇÃO</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="nivel">Recuperação de Senha - Pergunta</label>
                                    <select class="form-control" id="pergunta" name="pergunta" required>
                                        <option value="">Selecione a pergunta</option>
                                        <option value="Qual é o nome do seu primeiro animal de estimação?">Qual é o nome do seu primeiro animal de estimação?</option>
                                        <option value="Em que cidade você nasceu?">Em que cidade você nasceu?</option>
                                        <option value="Qual é o nome do seu melhor amigo de infância?">Qual é o nome do seu melhor amigo de infância?</option>
                                        <option value="Qual é o nome da escola onde você estudou no ensino fundamental?">Qual é o nome da escola onde você estudou no ensino fundamental?</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="resposta">Resposta</label>
                                    <input type="text" class="form-control" id="resposta" name="resposta" placeholder="Digite a resposta" required>
                                </div>
                                <div class="form-group">
                                    <label for="usuario">Usuário</label>
                                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Digite o usuário" required>
                                </div>

                                <div class="form-group">
                                    <label for="senha">Senha</label>
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite a senha" required>
                                </div>

                                <button type="submit" name="btnCadUsuario" class="btn btn-primary btn-block">Cadastrar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-md-8">
                    <h3 class="text-center mb-4">Usuários Cadastrados</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Usuário</th>
                                <th>Nível</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($usuarios)) {
                                foreach ($usuarios as $usuario) {
                                    echo "<tr>
                                    <td>{$usuario['nome']}</td>
                                    <td>{$usuario['email']}</td>
                                    <td>{$usuario['usuario']}</td>
                                    <td>{$usuario['nivel']}</td>
                                </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Nenhum usuário encontrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- 
    níveis 
    
    ADM 01
    NOC 02
    ATENDIMENTO 03
    VISUALIZAÇÃO 04
     
     
     -->
    </body>

    </html>
<?php

} else {
    $_SESSION['msg'] = "Área restrita";
    header("Location: main.php");
} ?>
<?php

session_start();
var_dump($_SESSION); // Verifica se o usuário está autenticado
if (empty($_SESSION['id'])) {
    header("Location: main.php"); // Redireciona para a página de login
    exit;
}


// Defina $shortLink como uma string vazia no início
$shortLink = '';

include_once 'conexao.php';

// Função para gerar código curto
function generateShortCode($length = 6)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

// Definindo variáveis para paginação
$linksPorPagina = 20; // Número de links por página
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Obtém a página atual
$offset = ($paginaAtual - 1) * $linksPorPagina; // Calcula o deslocamento

// Consultar o total de links
$totalLinksStmt = $pdo->query("SELECT COUNT(*) FROM short_links");
$totalLinks = $totalLinksStmt->fetchColumn();
$totalPaginas = ceil($totalLinks / $linksPorPagina); // Total de páginas

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

// Verificar se um código de contrato foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoContrato = $_POST['codigo_contrato'];

    // Verificar se o código de contrato foi preenchido
    if (!empty($codigoContrato)) {
        // Gerar um código curto e armazenar no banco de dados
        $shortCode = generateShortCode();

        // Preparar a inserção no banco de dados
        $stmt = $pdo->prepare("INSERT INTO short_links (codigo_contrato, short_code, created_at) VALUES (:codigo_contrato, :short_code, NOW())");
        if ($stmt->execute(['codigo_contrato' => $codigoContrato, 'short_code' => $shortCode])) {
            // Gerar o link mascarado
            $shortLink = "https://###/ASSINATURA-DIGITAL/ntc.php?code=" . $shortCode;
        } else {
            echo "Erro ao inserir no banco de dados.";
        }
    } else {
        echo "Por favor, insira um código de contrato.";
    }
}

// Buscar os links com limite e deslocamento
$links = [];
$query = "SELECT * FROM short_links ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $linksPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/encurtador.css">
    <title>Encurtador de Links - Código de Contrato</title>
</head>

<body>
    <div class="encurtador">
        <div class="logo"><img src="img/logonovo.png" alt=""></div>
        <h5>Gerar Link Mascarado para Código de Contrato</h5>
        <div class="bt">
            <?php
            session_start();

            if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] = 01) {
            ?>
                <button class="btn" onclick="window.location.href='cadastrar_user.php'">Cadastrar Usuário</button>
            <?php
            }

            ?>

            <button class="btn_v" onclick="window.location.href='sair.php'">Sair</button>

        </div>
        <form method="POST" action="">
            <label for="codigo_contrato">Insira o Código do Contrato:</label>
            <input type="text" name="codigo_contrato" id="codigo_contrato" required>
            <button type="submit" class="submit">Gerar Link</button>
        </form>

        <div class="gerador">
            <?php
            // Exibir o link encurtado apenas se $shortLink não estiver vazio
            if (!empty($shortLink)) {
                echo "Link encurtado: <a href='$shortLink'>$shortLink</a>";
            }
            ?>
        </div>

        <h4>Últimos Links Criados</h4>
        <table>
            <thead>
                <tr>
                    <th>Código Curto</th>
                    <th>Código do Contrato</th>
                    <th>Data de Criação</th>
                    <th>Contador de Cliques</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link): ?>
                    <tr>
                        <td><?php echo "https://###/ASSINATURA-DIGITAL/ntc.php?code=" . htmlspecialchars($link['short_code']); ?></td>
                        <td><?php echo htmlspecialchars($link['codigo_contrato']); ?></td>
                        <td><?php echo htmlspecialchars($link['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($link['click_count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Links de Paginação -->
        <div class="pagination">
            <?php if ($paginaAtual > 1): ?>
                <a href="?pagina=<?php echo $paginaAtual - 1; ?>">&laquo; Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>" class="<?php echo ($i == $paginaAtual) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($paginaAtual < $totalPaginas): ?>
                <a href="?pagina=<?php echo $paginaAtual + 1; ?>">Próximo &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
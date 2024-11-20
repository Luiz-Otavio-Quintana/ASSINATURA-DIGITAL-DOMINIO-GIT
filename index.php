<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura Digital</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="img/logonovo.png" alt="Netcol" class="central-image">
        </div>
        <div class="button">
            <button class="btn-proceder" onclick="proceedToSignature()">Proceder para assinatura digital</button>
        </div>
        <?php
        // Verifica se o código do contrato foi passado via GET
        if (isset($_GET['codigo_contrato'])) {
            $codigoContrato = htmlspecialchars($_GET['codigo_contrato']); // Sanitiza o código
            echo "<input type='hidden' id='codigoContrato' value='$codigoContrato'>";
        } else {
            echo "<p>O código do contrato não foi fornecido.</p>";
        }
        ?>
    </div>

    <script>
        function proceedToSignature() {
            // Obtém o código do contrato
            var codigoContrato = document.getElementById('codigoContrato').value;
            // Aqui você pode redirecionar ou realizar a ação desejada
            // Exemplo: redirecionar para outra página com o código do contrato
            window.location.href = 'assinatura.php?codigo_contrato=' + codigoContrato;
        }
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura Digital</title>
    <link rel="stylesheet" href="css/assinatura.css">
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="img/logonovo.png" alt="">
        </div>
        <h1>Assinatura Digital</h1>
        <?php
        // Verifica se o código do contrato foi passado via GET
        if (isset($_GET['codigo_contrato'])) {
            $codigoContrato = htmlspecialchars($_GET['codigo_contrato']);
            echo "<p>Código do Contrato: <strong>$codigoContrato</strong></p>";
        } else {
            echo "<p>Erro: O código do contrato não foi fornecido.</p>";
            exit; // Adiciona saída caso não haja código de contrato
        }
        ?>
        <div class="signature-area">
            <p>Por favor, assine o contrato digitalmente abaixo:</p>
            <div id="palco" style="position: relative; width: 400px; height: 200px;">
                <canvas id="signatureCanvas" width="400" height="200" style="z-index: 1; position: absolute; left: 0; top: 0;"></canvas>
                <canvas id="guideCanvas" width="400" height="200" style="z-index: 0; position: absolute; left: 0; top: 0; pointer-events: none;"></canvas>
            </div>
            <button id="clearButton">Limpar</button>
            <button id="saveButton">Proceder para Selfie</button>
        </div>

        <script>
            var signatureCanvas = document.getElementById('signatureCanvas');
            var signatureContext = signatureCanvas.getContext('2d');
            var guideCanvas = document.getElementById('guideCanvas');
            var guideContext = guideCanvas.getContext('2d');
            var drawing = false;

            // Função para desenhar a linha de orientação e o texto
            function drawGuideLine() {
                guideContext.beginPath();
                guideContext.moveTo(20, 100); // Ponto inicial (x, y)
                guideContext.lineTo(380, 100); // Ponto final (x, y)
                guideContext.strokeStyle = 'gray'; // Cor da linha
                guideContext.lineWidth = 2; // Espessura da linha
                guideContext.setLineDash([5, 5]); // Linha pontilhada
                guideContext.stroke(); // Aplica a linha no canvas auxiliar

                // Texto explicativo
                guideContext.font = '16px Arial';
                guideContext.fillStyle = 'gray';
                guideContext.fillText('Assine aqui', 160, 90);
            }

            // Desenha a linha de orientação na inicialização
            drawGuideLine();

            // Função para obter a posição do mouse
            function getMousePos(canvas, evt) {
                var rect = canvas.getBoundingClientRect();
                return {
                    x: evt.clientX - rect.left,
                    y: evt.clientY - rect.top
                };
            }

            // Funções de desenho
            function startDrawing(e) {
                var pos = getMousePos(signatureCanvas, e);
                drawing = true;
                signatureContext.beginPath();
                signatureContext.moveTo(pos.x, pos.y);
            }

            function draw(e) {
                if (!drawing) return;
                var pos = getMousePos(signatureCanvas, e);
                signatureContext.lineTo(pos.x, pos.y);
                signatureContext.stroke();
            }

            function stopDrawing() {
                drawing = false;
                signatureContext.closePath();
            }

            // Eventos para mouse
            signatureCanvas.addEventListener('mousedown', startDrawing);
            signatureCanvas.addEventListener('mousemove', draw);
            signatureCanvas.addEventListener('mouseup', stopDrawing);
            signatureCanvas.addEventListener('mouseout', stopDrawing);

            // Eventos para toque
            signatureCanvas.addEventListener('touchstart', function(e) {
                e.preventDefault();
                var touch = e.touches[0];
                startDrawing({
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
            });

            signatureCanvas.addEventListener('touchmove', function(e) {
                e.preventDefault();
                var touch = e.touches[0];
                draw({
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
            });

            signatureCanvas.addEventListener('touchend', function(e) {
                e.preventDefault();
                stopDrawing();
            });

            // Limpar a assinatura
            document.getElementById('clearButton').addEventListener('click', function() {
                signatureContext.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                localStorage.removeItem('savedSignature');
            });

            // Salvar a assinatura
            document.getElementById('saveButton').addEventListener('click', function() {
                var dataURL = signatureCanvas.toDataURL();
                /* console.log("Data URL: ", dataURL); */

                // Verifica se o canvas está vazio
                var blankCanvas = document.createElement('canvas');
                blankCanvas.width = signatureCanvas.width;
                blankCanvas.height = signatureCanvas.height;
                var blankContext = blankCanvas.getContext('2d');
                if (dataURL === blankCanvas.toDataURL()) {
                    alert('Por favor, assine o contrato antes de prosseguir.');
                } else {
                    localStorage.setItem('savedSignature', dataURL);
                    alert('Assinatura salva!');
                    var url = 'self.php?codigo_contrato=' + encodeURIComponent('<?php echo $codigoContrato; ?>');
                    window.location.href = url;
                }
            });
        </script>

        <script>
            window.onload = function() {
                var savedSignature = localStorage.getItem('savedSignature');
                if (savedSignature) {
                    /* console.log('Assinatura recuperada do localStorage:', savedSignature);
                     */
                    var img = new Image();
                    img.onload = function() {
                        context.drawImage(img, 0, 0);
                        /* console.log('Assinatura carregada no canvas');
                         */
                    };
                    img.src = savedSignature;
                } else {
                    /* console.log('Nenhuma assinatura salva encontrada no localStorage');
                     */
                }
            };
        </script>
</body>

</html>
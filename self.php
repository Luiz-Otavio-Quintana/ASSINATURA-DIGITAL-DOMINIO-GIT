<?php
// Adiciona os cabeçalhos para permitir requisições CORS
header("Access-Control-Allow-Origin: *"); // ou use a origem específica em produção
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Vary: Origin"); // Adicionado para melhorar o cache das respostas

// Se a requisição for do tipo OPTIONS, finaliza a execução
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {

    // Conexão com o banco de dados
    include_once 'conexao.php';

    // Verifica se há um código na URL
    if (isset($_GET['codigo_contrato'])) {
        $codigo = $_GET['codigo_contrato'];

        // Verifica se o código existe na base de dados
        $stmt = $pdo->prepare("SELECT * FROM short_links WHERE codigo_contrato = :codigo");
        $stmt->execute(['codigo' => $codigo]);

        $contrato = $stmt->fetch();

        if (!$contrato) {
            // Código não encontrado, exibe mensagem de erro
            echo '<div style="border: 1px solid red; padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 5px; text-align: center;">
        <img src="img/alert.png" alt="Advertência" style="width: 50px; vertical-align: middle; margin-right: 10px;">
        <strong>Operação ilegal! Code 401.</strong>
      </div>';
            exit;
        } else {
        }
    } else {
        // Se não houver código, exibe mensagem de erro
        echo '<div style="border: 1px solid red; padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 5px; text-align: center;">
        <img src="img/alert.png" alt="Advertência" style="width: 50px; vertical-align: middle; margin-right: 10px;">
        <strong>Operação ilegal! Code 402.</strong>
      </div>';
    }
} catch (PDOException $e) {
    echo 'Erro na conexão: ' . $e->getMessage();
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura e Captura de Foto</title>
    <link rel="stylesheet" href="css/self.css">
</head>

<body>
    <div class="container">
        <div class="logo"><img src="img/logonovo.png" alt=""></div>
        <h1>Captura de Assinatura e Foto</h1>
        <div class="signature-display">
            <p>Abaixo está sua assinatura digital:</p>
            <canvas id="signatureCanvas" width="400" height="300" style="border: 1px solid #000;"></canvas>
            <br>
        </div>

        <div class="photo-capture">
            <h2>Captura de Foto</h2>
            <h4>Exemplo de foto Válida</h4>
            <div class="captura"><img src="img/assinatura.png" alt="" srcset=""></div>

            <label id="upload">
                <input type="checkbox" id="hasDigitalDocument" onclick="toggleUploadField()"> Possuo documento digital
            </label>

            <!-- Campo de upload que aparece ao selecionar o checkbox -->
            <div id="uploadField" style="display: none; margin-top: 10px;">
                <label for="digitalDocument">Enviar documento digital:</label>
                <label id="pdf">Formatos aceitos .PDF somente</label>
                <input type="file" id="digitalDocument" accept="application/pdf" onchange="previewDocument()">
            </div>

            <!-- Área de visualização do documento carregado -->
            <div id="documentPreview" style="display: none; margin-top: 10px;">
                <img id="imagePreview" style="display: none; max-width: 100%; max-height: 300px;">
                <iframe id="pdfPreview" style="display: none; width: 100%; height: 400px;"></iframe>
            </div>

            <video id="video" width="400" height="300" autoplay></video>
            <button id="captureButton">Capturar Foto</button>
            <canvas id="photoCanvas" width="400" height="300" style="display:none;"></canvas>
            <img id="photo" alt="Sua foto" style="display:none;">
        </div>



        <div class="contract-code">
            <h2>Código do Contrato</h2>
            <input type="text" id="contractCode" placeholder="Código do contrato" required readonly>
        </div>

        <button id="sendButton" onclick="enviarDados()">Enviar Dados</button>
        <p id="aguardeMessage" style="display: none; color: Red; text-align: center; font-weight: bold;">Aguarde... Este Processo Pode Demorar Alguns Segundos!</p>

        <script>
            function enviarDados() {
                // Exibe a mensagem "..."
                document.getElementById("aguardeMessage").style.display = "block";

                // Desativa o botão para impedir múltiplos cliques
                const botao = document.getElementById("sendButton");
                botao.disabled = true;


            }
        </script>
    </div>

    <script>
        const video = document.getElementById('video');
        const photoCanvas = document.getElementById('photoCanvas');
        const context = photoCanvas.getContext('2d');

        // Solicita permissão e acessa a câmera
        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(stream => {
                console.log("Câmera acessada com sucesso.");

                video.srcObject = stream;
            })
            .catch(err => {
                console.error("Erro ao acessar a câmera: ", err);
                console.error("Erro ao acessar a câmera:", err);
                alert("Erro ao acessar a câmera. Verifique as permissões nas configurações do navegador.");
            });

        // Captura a foto
        document.getElementById('captureButton').addEventListener('click', function() {
            context.drawImage(video, 0, 0, photoCanvas.width, photoCanvas.height);
            const photo = document.getElementById('photo');
            photo.src = photoCanvas.toDataURL();
            photo.style.display = 'block';
            console.log("Foto capturada:", photo.src);

            if (!photo.src || photo.src === 'data:,') {
                alert('Erro: A selfie não foi capturada corretamente.');
            }
        });

        // Converte DataURL para Blob
        function dataURLToBlob(dataURL) {
            const byteString = atob(dataURL.split(',')[1]);
            const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
            const ab = new Uint8Array(byteString.length);
            for (let i = 0; i < byteString.length; i++) {
                ab[i] = byteString.charCodeAt(i);
            }
            return new Blob([ab], {
                type: mimeString
            });
        }
        const sendData = async function(token) {
            const signatureCanvas = document.getElementById('signatureCanvas');
            const photoCanvas = document.getElementById('photoCanvas');
            const contractCode = document.getElementById('contractCode').value;
            const documentInput = document.getElementById("digitalDocument");

            // Verifica se todos os campos obrigatórios estão preenchidos
            if (!signatureCanvas || !photoCanvas || !contractCode.trim()) {
                alert("Assinatura, selfie e código de contrato são obrigatórios.");
                return;
            }
            if (!photo.src) {
                alert("Por favor, Precisamos da Selfie.");
                location.reload(); // Recarrega a página
                return;
            }




            const signatureBlob = dataURLToBlob(signatureCanvas.toDataURL());
            const formData = new FormData();
            formData.append('assinatura', signatureBlob, 'assinatura.png');
            formData.append('contrato', contractCode);

            // Verifica se o documento digital deve ser enviado
            if (document.getElementById("hasDigitalDocument").checked) {
                const file = documentInput.files[0];

                // Valida se o arquivo foi selecionado e se o tipo é permitido
                if (!file) {
                    alert("Por favor, carregue um documento digital.");
                    location.reload(); // Recarrega a página
                    return;
                }


                const validTypes = ["application/pdf"];
                if (!validTypes.includes(file.type)) {
                    alert("O arquivo deve ser um PDF.");
                    location.reload(); // Recarrega a página
                    return;
                }

                // Processa o PDF
                let pdfImage;
                try {
                    if (file.type === "application/pdf") {
                        pdfImage = await convertPdfToImage(file);
                    } else {
                        pdfImage = await getImageBlob(file);
                    }
                } catch (error) {
                    console.error("Erro ao processar o documento:", error);
                    alert("Erro ao processar o documento. Verifique se é uma imagem válida ou um PDF.");
                    return;
                }

                const photoBlob = await getPhotoBlob(photoCanvas);
                if (photoBlob) {
                    const selfieCanvas = document.createElement('canvas');
                    const context = selfieCanvas.getContext('2d');

                    // Define proporções de largura e altura para selfie e documento
                    const selfieWidthPercentage = 0.5;
                    const docWidthPercentage = 0.5;

                    // Configura tamanhos do canvas combinado
                    selfieCanvas.width = Math.max(photoCanvas.width, pdfImage.width);
                    selfieCanvas.height = Math.max(photoCanvas.height, pdfImage.height);

                    // Desenha a selfie e o documento no canvas
                    const selfieWidth = selfieCanvas.width * selfieWidthPercentage;
                    const adjustedSelfieHeight = (photoCanvas.height / photoCanvas.width) * selfieWidth;

                    // Verifica se photoCanvas e pdfImage são válidos
                    if (photoCanvas && pdfImage) {
                        context.drawImage(photoCanvas, 0, (selfieCanvas.height - adjustedSelfieHeight) / 2, selfieWidth, adjustedSelfieHeight);
                        context.drawImage(pdfImage, selfieWidth, 0, selfieCanvas.width - selfieWidth, pdfImage.height);

                        selfieCanvas.toBlob(async (blob) => {
                            if (blob) {
                                formData.append('selfie', blob, 'selfie.png');
                                console.log("Selfie combinada e pronta para enviar.");

                                formData.append('token', token);
                                formData.append('sys', 'MK0');

                                await sendToApi(formData, contractCode);
                            } else {
                                alert("Erro ao criar a imagem combinada.");
                            }
                        }, 'image/png');
                    } else {
                        alert("Erro: Canvas ou imagem não válidos.");
                    }
                }
            } else {
                const photoBlob = await getPhotoBlob(photoCanvas);
                if (photoBlob) {
                    formData.append('selfie', photoBlob, 'selfie.png');
                    console.log("Selfie disponível e pronta para enviar.");

                    formData.append('token', token);
                    formData.append('sys', 'MK0');

                    await sendToApi(formData, contractCode);
                } else {
                    alert("Erro ao obter o blob da selfie.");
                }
            }
        };

        // Função para converter PDF em imagem
        async function convertPdfToImage(file) {
            return new Promise((resolve, reject) => {
                const fileReader = new FileReader();
                fileReader.onload = async (event) => {
                    const typedArray = new Uint8Array(event.target.result);
                    const pdf = await pdfjsLib.getDocument(typedArray).promise;

                    const page = await pdf.getPage(1);
                    const viewport = page.getViewport({
                        scale: 2.0
                    });

                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const context = canvas.getContext('2d');

                    await page.render({
                        canvasContext: context,
                        viewport
                    }).promise;
                    resolve(canvas);
                };
                fileReader.onerror = (error) => {
                    console.error("Erro ao ler o arquivo PDF:", error);
                    reject(error);
                };
                fileReader.readAsArrayBuffer(file);
            });
        }

        // Função para obter blob de imagem
        async function getImageBlob(file) {
            return new Promise((resolve, reject) => {
                const fileReader = new FileReader();
                fileReader.onload = () => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const context = canvas.getContext('2d');
                        context.drawImage(img, 0, 0);
                        canvas.toBlob((blob) => {
                            if (blob) {
                                resolve(blob);
                            } else {
                                reject("Erro ao converter a imagem em blob.");
                            }
                        }, 'image/png');
                    };
                    img.onerror = () => reject("Erro ao carregar a imagem.");
                    img.src = fileReader.result;
                };
                fileReader.onerror = (error) => reject("Erro ao ler o arquivo da imagem: " + error);
                fileReader.readAsDataURL(file);
            });
        }

        // Função para obter o blob da selfie
        async function getPhotoBlob(photoCanvas) {
            return new Promise((resolve) => {
                photoCanvas.toBlob((blob) => {
                    resolve(blob);
                }, 'image/png');
            });
        }

        // Função para enviar dados para a API
        async function sendToApi(formData, contractCode) {
            try {
                const response = await fetch(`https://api?token=${formData.get('token')}&contrato=${contractCode}`, {
                    method: 'POST',
                    body: formData,
                    mode: 'cors'
                });

                if (response.ok) {
                    const result = await response.json();
                    console.log("Resultado da resposta:", result);

                    if (result.response && result.response.status === "ERRO") {
                        alert(result.response.mensagem);
                        window.location.href = `error.html?mensagem=${encodeURIComponent(result.response.mensagem)}`;
                    } else {
                        alert(result.mensagem);
                        window.location.href = `success.html?codigo_contrato=${contractCode}`;
                    }
                } else {
                    alert("Erro ao enviar dados: " + response.statusText);
                }
            } catch (error) {
                console.error("Erro ao enviar dados:", error);
                alert("Erro ao enviar dados. Tente novamente mais tarde.");
            }
        };

        // Autenticação
        async function authenticate() {
            const userToken = '###';
            const password = '###';
            const cd_servico = '###';

            console.log("Iniciando autenticação...");

            try {
                const response = await fetch(`apitoken=${userToken}&password=${password}&cd_servico=${cd_servico}`);
                const data = await response.json();

                console.log("Resposta de autenticação:", data);

                if (data && data.Token) {
                    sendData(data.Token);
                } else {
                    alert('Erro durante o processo: Token de autenticação não obtido.');
                }
            } catch (error) {
                console.error("Erro durante a autenticação:", error);

                alert('Erro durante a autenticação: ' + error.message);
            }
        }

        // Enviar dados após autenticação
        document.getElementById('sendButton').addEventListener('click', function() {
            const contractCode = document.getElementById('contractCode').value;
            console.log("Código do contrato:", contractCode);

            if (!contractCode) {
                alert('Por favor, insira o código do contrato.');
                return;
            }
            authenticate();
        });

        // Obter o código do contrato da URL
        function getContractCodeFromURL() {
            const params = new URLSearchParams(window.location.search);
            const codigoContrato = params.get('codigo_contrato');
            console.log("Código do contrato da URL:", codigoContrato);

            if (codigoContrato) {
                document.getElementById('contractCode').value = codigoContrato;
            }
        }

        window.onload = function() {
            const savedSignature = localStorage.getItem('savedSignature');
            const signatureCanvas = document.getElementById('signatureCanvas');
            console.log("Assinatura salva encontrada:", savedSignature);

            if (savedSignature) {
                const img = new Image();
                img.onload = function() {
                    const ctx = signatureCanvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    console.log("Assinatura carregada no canvas.");

                };
                img.src = savedSignature;
            } else {
                document.querySelector('.signature-display').innerHTML = '<p>Nenhuma assinatura encontrada.</p>';
            }

            getContractCodeFromURL();
        };

        function toggleUploadField() {
            const uploadField = document.getElementById("uploadField");
            const hasDigitalDocument = document.getElementById("hasDigitalDocument");
            uploadField.style.display = hasDigitalDocument.checked ? "block" : "none";

            // Oculta a pré-visualização se o checkbox for desmarcado
            if (!hasDigitalDocument.checked) {
                document.getElementById("documentPreview").style.display = "none";
                document.getElementById("imagePreview").style.display = "none";
                document.getElementById("pdfPreview").style.display = "none";
            }
        }

        function previewDocument() {
            const file = document.getElementById("digitalDocument").files[0];
            const documentPreview = document.getElementById("documentPreview");
            const imagePreview = document.getElementById("imagePreview");
            const pdfPreview = document.getElementById("pdfPreview");

            if (file) {
                const fileType = file.type;

                // Reseta a visualização
                imagePreview.style.display = "none";
                pdfPreview.style.display = "none";

                if (fileType.startsWith("image/")) {
                    // Se o arquivo for uma imagem
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                } else if (fileType === "application/pdf") {
                    // Se o arquivo for um PDF
                    const fileURL = URL.createObjectURL(file);
                    pdfPreview.src = fileURL;
                    pdfPreview.style.display = "block";
                }

                // Exibe a área de pré-visualização
                documentPreview.style.display = "block";
            }
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>

</body>
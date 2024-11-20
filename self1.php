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
        <div class="logo"><img src="img/logonovo.png" alt="Logotipo"></div>
        <h1>Captura de Assinatura e Foto</h1>

        <div class="signature-display">
            <p>Abaixo está sua assinatura digital:</p>
            <canvas id="signatureCanvas" width="400" height="200" style="border: 1px solid #000;"></canvas>
        </div>

        <div class="photo-capture">
            <h2>Captura de Foto</h2>
            <video id="video" width="400" height="300" autoplay></video>
            <button id="captureButton">Capturar Foto</button>
            <canvas id="photoCanvas" width="400" height="300" style="display:none;"></canvas>
            <img id="photo" alt="Sua foto" style="display:none;">
        </div>

        <div class="contract-code">
            <h2>Código do Contrato</h2>
            <input type="text" id="contractCode" placeholder="Código do contrato" required readonly>
        </div>

        <button id="sendButton">Enviar Dados</button>
    </div>

    <script>
        const video = document.getElementById('video');
        const photoCanvas = document.getElementById('photoCanvas');
        const context = photoCanvas.getContext('2d');

        // Acessa a câmera
        navigator.mediaDevices.getUserMedia({
                video: true
            })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                console.error("Erro ao acessar a câmera:", err);
                alert("Erro ao acessar a câmera. Verifique as permissões nas configurações do navegador.");
            });

        // Captura a foto
        document.getElementById('captureButton').addEventListener('click', function() {
            context.drawImage(video, 0, 0, photoCanvas.width, photoCanvas.height);
            const photo = document.getElementById('photo');
            photo.src = photoCanvas.toDataURL();
            photo.style.display = 'block';
            if (!photo.src || photo.src === 'data:,') {
                alert('Erro: A selfie não foi capturada corretamente.');
            }
        });

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

        async function sendData(token) {
            const signatureCanvas = document.getElementById('signatureCanvas');
            const signatureBlob = dataURLToBlob(signatureCanvas.toDataURL());
            const contractCode = document.getElementById('contractCode').value;
            const photoBlob = dataURLToBlob(photoCanvas.toDataURL());

            const formData = new FormData();
            formData.append('assinatura', signatureBlob, 'assinatura.png');
            formData.append('contrato', contractCode);
            formData.append('selfie', photoBlob, 'selfie.png');
            formData.append('token', token);
            formData.append('sys', 'MK0');

            try {
                const response = await fetch(`https://sistema.netcol.com.br/mk/WSMKAceiteSelfieAssinatura.rule?token=${token}&contrato=${contractCode}`, {
                    method: 'POST',
                    body: formData,
                    mode: 'cors'
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.response && result.response.status === "ERRO") {
                        alert(result.response.mensagem);
                        window.location.href = `error.html?mensagem=${encodeURIComponent(result.response.mensagem)}`;
                    } else {
                        alert('Contrato aceito com sucesso: ' + result.response.mensagem);
                        window.location.href = `success.html?codigo_contrato=${result.response.codigo_contrato}&nome_cliente=${encodeURIComponent(nomeCliente)}`;
                    }
                } else {
                    const errorMessage = await response.json();
                    alert(errorMessage.response.mensagem || 'Erro desconhecido ao enviar os dados.');
                }
            } catch (error) {
                alert('Erro: ' + error.message);
            }
        }

        async function authenticate() {
            const userToken = '4d1cf9dcd5e22635057fbc78c2b0da74';
            const password = '3159210f1083894';
            const cd_servico = '9999';

            try {
                const response = await fetch(`http://sistema.netcol.com.br/mk/WSAutenticacao.rule?sys=MK0&token=${userToken}&password=${password}&cd_servico=${cd_servico}`);
                const data = await response.json();
                if (data && data.Token) {
                    sendData(data.Token);
                } else {
                    alert('Erro: Token de autenticação não obtido.');
                }
            } catch (error) {
                alert('Erro durante a autenticação: ' + error.message);
            }
        }

        document.getElementById('sendButton').addEventListener('click', function() {
            const contractCode = document.getElementById('contractCode').value;
            if (!contractCode) {
                alert('Por favor, insira o código do contrato.');
                return;
            }
            authenticate();
        });

        function getContractCodeFromURL() {
            const params = new URLSearchParams(window.location.search);
            const codigoContrato = params.get('codigo_contrato');
            if (codigoContrato) {
                document.getElementById('contractCode').value = codigoContrato;
            }
        }

        window.onload = function() {
            const savedSignature = localStorage.getItem('savedSignature');
            const signatureCanvas = document.getElementById('signatureCanvas');
            if (savedSignature) {
                const img = new Image();
                img.onload = function() {
                    const ctx = signatureCanvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                };
                img.src = savedSignature;
            } else {
                document.querySelector('.signature-display').innerHTML = '<p>Nenhuma assinatura encontrada.</p>';
            }

            getContractCodeFromURL();
        };
    </script>
</body>

</html>
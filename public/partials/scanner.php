<!-- File: public/partials/scanner.php -->
<div id="scanner-container">
    <video id="preview" width="100%"></video>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/instascan/1.0.0/instascan.min.js"></script>
<script>
    let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
    scanner.addListener('scan', function (content) {
        fetch('atualizar_rastreio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo: content, evento: 'Escaneado no depósito' })
        }).then(() => alert('Evento registrado!'));
    });
    Instascan.Camera.getCameras().then(cameras => {
        if (cameras.length > 0) scanner.start(cameras[0]);
    });
</script>
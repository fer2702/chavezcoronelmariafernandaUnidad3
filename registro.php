<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// CONEXIÓN A LA BASE DE DATOS
$host = "localhost";
$user = "root";
$pass = "";
$db = "seguridad_bd";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4"); 

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar reCAPTCHA
    $recaptcha_secret = '6Leks14rAAAAAGsgufrIRc-zdzSnOSqJYaAHTQ4n';
    $recaptcha_response = $_POST['g-recaptcha-response'];

    if (!$recaptcha_response) {
        $mensaje = "❌ Error: Verifica el reCAPTCHA.";
    } else {
        $response = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response"
        );
        $responseKeys = json_decode($response, true);

        if (intval($responseKeys["success"]) !== 1) {
            $mensaje = "❌ Error: No se pudo verificar el reCAPTCHA.";
        } else {
            // Obtención de datos
            $usuario = $_POST["usuario"];
            $correo = $_POST["correo"];
            $password = $_POST["contrasena"];

            // Verificar si el correo ya está registrado
            $check_sql = "SELECT id FROM usuarios WHERE correo = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $correo);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $mensaje = "❌ El correo ya está registrado. Intenta con otro.";
            } else {
                // Hashear la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insertar usuario
                $sql = "INSERT INTO usuarios (usuario, correo, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $usuario, $correo, $password_hash);

                if ($stmt->execute()) {
                    // Envío de correo de bienvenida
                    $mail = new PHPMailer(true);
                    $mail->CharSet = 'UTF-8';

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'mariafernandachavezcoronel11@gmail.com';
                        $mail->Password = 'mjof qxud ulfd fzxj'; // App Password
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('GasesySoldadurasExpress@gmail.com', 'Soldaduras y Gases Express');
                        $mail->addAddress($correo, $usuario);

                        $mail->isHTML(true);
                        $mail->Subject = '¡Bienvenido a Tu Cuenta!';
                        $mail->Body = "
                            <h2>Hola, $usuario 👋</h2>
                            <p>Gracias por registrarte en nuestra plataforma.</p>
                            <p>Estamos felices de tenerte con nosotros.</p>
                            <p>¡Disfruta la experiencia!</p>
                        ";

                        $mail->send();
                        $mensaje = "✅ ¡Usuario registrado correctamente! Se ha enviado un correo de bienvenida.";
                    } catch (Exception $e) {
                        $mensaje = "Usuario registrado, pero error al enviar correo: {$mail->ErrorInfo}";
                    }

                    $stmt->close();
                } else {
                    $mensaje = "❌ Error al registrar usuario ❌";
                }
            }

            $check_stmt->close();
        }
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registro de Usuario</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }
        .container {
            background-color: #1f1f1f;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(255, 102, 0, 0.7);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeIn 1s ease-out;
            border: 2px solid #ff6600;
        }
        h2 {
            color: #ff6600;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2rem;
            text-shadow: 0 0 8px #ffcc33;
            animation: bounceIn 0.8s ease-out;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            margin: 12px 0;
            border: 2px solid #333;
            border-radius: 12px;
            font-size: 16px;
            background-color: #222;
            color: #fff;
            font-weight: 500;
            transition: border-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #ffcc33;
            box-shadow: 0 0 10px #ffcc33;
            outline: none;
            background-color: #121212;
        }
        input[type="submit"] {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 0 10px #ff6600;
        }
        input[type="submit"]:hover {
            background-color: #e65c00;
            transform: scale(1.05);
            box-shadow: 0 0 20px #ffcc33;
        }
        p {
            margin-top: 20px;
            font-weight: 600;
            color: #ffcc33;
            font-size: 0.95rem;
            animation: fadeIn 1s ease-in-out;
            text-shadow: 0 0 5px #ffcc33;
        }
        .top-bar {
            margin-top: 18px;
        }
        .btn-inicio {
            display: inline-block;
            padding: 10px 24px;
            background-color: #333;
            color: #ff6600;
            font-weight: 600;
            border-radius: 25px;
            text-decoration: none;
            box-shadow: 0 0 8px #ff6600;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .btn-inicio:hover {
            background-color: #ffcc33;
            color: #121212;
            box-shadow: 0 0 15px #ffcc33;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes bounceIn {
            0% { transform: scale(0.5); opacity: 0; }
            60% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        <?php if ($mensaje) echo "<p>$mensaje</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="usuario" placeholder="Nombre de usuario" required>
            <input type="email" name="correo" placeholder="📧 Correo electrónico" required>
            <input type="password" name="contrasena" placeholder="🔒 Contraseña" required>

            <div class="g-recaptcha" data-sitekey="6Leks14rAAAAAGb7mpdTX5HQfeJNXubGxqRBm70M"></div>

            <input type="submit" value="Registrarse">
            <div class="top-bar">
                <a href="login.php" class="btn-inicio">🏠 Iniciar sesión</a>
            </div>
        </form>
    </div>
</body>
</html>


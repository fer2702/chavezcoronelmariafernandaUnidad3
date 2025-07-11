<?php 
session_start();

// Validación de sesión admin (puedes dejarlo comentado para pruebas)
// if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
//     header("Location: index.php");
//     exit();
// }

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$db = "seguridad_bd";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

// Procesar formulario: aceptar o suspender usuario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = (int)$_POST["id_usuario"]; // casteo a entero para seguridad

    if (isset($_POST["aceptar"])) {
        $conn->query("UPDATE usuarios SET estado = 'activo' WHERE id = $id_usuario");
    } elseif (isset($_POST["suspender"])) {
        $conn->query("UPDATE usuarios SET estado = 'suspendido' WHERE id = $id_usuario AND rol != 'admin'");
    }
}

// Obtener todos los usuarios
$resultado = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #121212;
            color: white;
            padding: 20px;
        }
        h1 {
            color: #ff6600;
            text-shadow: 0 0 5px #ffcc33;
        }
        .card {
            background: #1f1f1f;
            border: 2px solid #ff6600;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgba(255, 102, 0, 0.5);
        }
        .card h3 {
            margin: 0 0 10px;
            color: #ffcc33;
        }
        .acciones {
            margin-top: 10px;
        }
        .acciones form {
            display: inline-block;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-aceptar {
            background: #28a745;
            color: white;
        }
        .btn-suspender {
            background: #dc3545;
            color: white;
        }
        .estado {
            font-size: 0.9em;
            color: #ccc;
        }
    </style>
</head>
<body>
    <h1>👑 Panel de Administración</h1>

    <?php while ($row = $resultado->fetch_assoc()): ?>
        <div class="card">
            <h3><?= htmlspecialchars($row["usuario"]) ?> (<?= htmlspecialchars($row["correo"]) ?>)</h3>
            <p>Rol: <?= htmlspecialchars($row["rol"]) ?> | Estado: <span class="estado"><?= htmlspecialchars($row["estado"]) ?></span></p>

            <?php if ($row["rol"] !== "admin"): ?>
                <div class="acciones">
                    <form method="POST">
                        <input type="hidden" name="id_usuario" value="<?= (int)$row["id"] ?>">

                        <?php if ($row["estado"] === "pendiente" || $row["estado"] === "suspendido"): ?>
                            <button type="submit" name="aceptar" class="btn btn-aceptar">Aceptar</button>
                        <?php endif; ?>

                        <?php if ($row["estado"] === "activo"): ?>
                            <button type="submit" name="suspender" class="btn btn-suspender" onclick="return confirm('¿Suspender este usuario?')">Suspender</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php else: ?>
                <p><em>Este es el administrador principal.</em></p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</body>
</html>



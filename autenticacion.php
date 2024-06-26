<?php
session_start();

// Credenciales de acceso a la base datos
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'login-php';

// Conexión a la base de datos
$conexion = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_error()) {
    // Si se encuentra error en la conexión
    exit('Fallo en la conexión de MySQL: ' . mysqli_connect_error());
}

// Se valida si se ha enviado información, con la función isset()
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['username'], $_POST['password'])) {
        // Si no hay datos muestra error y redirige
        exit('Por favor ingrese ambos campos de usuario y contraseña.');
    }

    // Evitar inyección SQL
    if ($stmt = $conexion->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
        // Parámetros de enlace de la cadena s
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->store_result();

        // Validar si lo ingresado coincide con la base de datos
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $password);
            $stmt->fetch();

            // Confirmar que la cuenta existe, ahora validar la contraseña
            // Utilizar password_verify para comparar contraseñas hashed
            if (password_verify($_POST['password'], $password)) {
                // La conexión sería exitosa, se crea la sesión
                session_regenerate_id();
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['name'] = $_POST['username'];
                $_SESSION['id'] = $id;
                header('Location: inicio.php');
            } else {
                // Contraseña incorrecta
                echo 'Contraseña incorrecta!';
            }
        } else {
            // Usuario incorrecto
            echo 'Usuario incorrecto!';
        }

        $stmt->close();
    } else {
        // Error en la preparación de la declaración SQL
        echo 'No se pudo preparar la declaración SQL!';
    }

    $conexion->close();
} else {
    echo 'Método HTTP no permitido.';
}
?>

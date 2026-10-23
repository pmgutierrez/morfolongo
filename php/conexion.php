<?php
// Conexión a MySQL con PDO. Ajustá usuario/clave si tu XAMPP/Laragon es distinto.
$host = '';
$base = '';
$user = '';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$base;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Verificá que MySQL esté encendido y que hayas importado bd/final_web.sql.');
}

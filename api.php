<?php
// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Cargar comandos autorizados
$allowed_commands = include('commands.php');

// Leer el cuerpo de la solicitud
$request_body = file_get_contents("php://input");
$data = json_decode($request_body, true);

if (!isset($data['command'])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta el parámetro 'command'"]);
    exit;
}

$command = $data['command'];

// Validar el comando
if (!in_array($command, $allowed_commands)) {
    http_response_code(403);
    echo json_encode(["error" => "Comando no autorizado"]);
    exit;
}

// Ejecutar el comando
try {
    $output = [];
    exec($command, $output, $return_var);

    if ($return_var !== 0) {
        http_response_code(500);
        echo json_encode(["error" => "Error al ejecutar el comando"]);
        exit;
    }

    // Respuesta exitosa
    echo json_encode(["output" => $output]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error interno: " . $e->getMessage()]);
}

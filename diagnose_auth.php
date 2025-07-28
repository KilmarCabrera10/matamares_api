<?php
// Script de diagnóstico para tokens

echo "=== DIAGNÓSTICO DE AUTENTICACIÓN ===\n";

// 1. Obtener un token fresco
$loginData = json_encode([
    'email' => 'admin@matamares.com',
    'password' => 'Admin123!'
]);

$context = stream_context_create([
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'method' => 'POST',
        'content' => $loginData
    ]
]);

echo "1. Obteniendo token fresco...\n";
$result = file_get_contents('http://localhost:8000/api/auth/login', false, $context);
$loginResponse = json_decode($result, true);

if (!$loginResponse || !isset($loginResponse['token'])) {
    echo "❌ Error obteniendo token\n";
    exit(1);
}

$token = $loginResponse['token'];
echo "✅ Token obtenido: " . substr($token, 0, 20) . "...\n\n";

// 2. Test de roles
echo "2. Probando endpoint /api/roles...\n";
$context = stream_context_create([
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ],
        'method' => 'GET',
        'ignore_errors' => true
    ]
]);

$result = file_get_contents('http://localhost:8000/api/roles', false, $context);
$statusLine = $http_response_header[0] ?? 'No status';
echo "Status: $statusLine\n";
echo "Response: " . substr($result, 0, 200) . "...\n\n";

// 3. Test de usuarios admin
echo "3. Probando endpoint /api/admin/users...\n";
$result = file_get_contents('http://localhost:8000/api/admin/users', false, $context);
$statusLine = $http_response_header[0] ?? 'No status';
echo "Status: $statusLine\n";
echo "Response: " . substr($result, 0, 200) . "...\n\n";

// 4. Test de user info
echo "4. Probando endpoint /api/user...\n";
$result = file_get_contents('http://localhost:8000/api/user', false, $context);
$statusLine = $http_response_header[0] ?? 'No status';
echo "Status: $statusLine\n";
echo "Response: " . substr($result, 0, 200) . "...\n\n";

// 5. Verificar token en base de datos
echo "5. Verificando tokens en base de datos...\n";
echo "Comando para verificar: php artisan tinker\n";
echo "Comando tinker: \\App\\Models\\User::find(1)->tokens()->count()\n";
?>

<?php
// legacy_clients.php

// Simulación de una "base de datos" de clientes del sistema antiguo
function get_legacy_database_data() {
    return [
        ['id' => 1, 'full_name' => 'Alex Pérez García', 'email' => 'alex.perez@example.com', 'company_id' => 'COM-001', 'password_hash' => '...', 'created_at' => '2020-01-15'],
        ['id' => 2, 'full_name' => 'Maria López', 'email' => 'maria.lopez@example.com', 'company_id' => 'COM-002', 'password_hash' => '...', 'created_at' => '2021-03-22'],
        ['id' => 3, 'full_name' => 'Carlos Sánchez', 'email' => 'carlos.sanchez@example.com', 'company_id' => 'COM-001', 'password_hash' => '...', 'created_at' => '2019-11-10'],
    ];
}

$isJson = isset($_GET['format']) && $_GET['format'] === 'json';

// Lógica para mostrar los clientes
$clients = get_legacy_database_data();

if($isJson) {
    $jsonClients = array_map(function($client) {

        //Separar el nombre completo en nombre y apellidos
        $nameParts = explode(' ', $client['full_name']);
        $firstName = array_shift($nameParts);
        $lastName = implode(' ', $nameParts);

        return [
            'legacy_id' => $client['id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $client['email'],
            'company_id' => $client['company_id'],
            'created_at' => $client['created_at'],
        ];
    }, $clients);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($jsonClients);
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo "<h1>Lista de Clientes Legacy</h1>";
echo "<ul>";
foreach ($clients as $client) {
    echo "<li>" . htmlspecialchars($client['full_name']) . " (" . htmlspecialchars($client['email']) . ")</li>";
}
echo "</ul>";


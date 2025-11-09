<?php

// CONFIGURAÇÃO DO BANCO DE DADOS // 

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'video_locadora');

//conexão
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

//Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

//Retornar a conexão
return $conn;
?>
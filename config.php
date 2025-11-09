<?php
// Arquivo: config.php — cria e retorna a conexão com o MySQL (mysqli)

// CONFIGURAÇÃO DO BANCO DE DADOS 
// Constantes para os parâmetros de conexão — ajuste conforme seu ambiente local
define('DB_HOST', 'localhost'); // Endereço do servidor de banco (geralmente localhost no XAMPP)
define('DB_USER', 'root');      // Usuário padrão do MySQL no XAMPP é 'root'
define('DB_PASS', '');          // Senha padrão do XAMPP costuma ser vazia
define('DB_NAME', 'video_locadora'); // Nome do banco de dados do projeto

// Abre a conexão com o MySQL usando mysqli (orientado a objeto)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); // Pode lançar erro se host/credenciais estiverem incorretos

// Verificar se houve erro ao conectar; em caso positivo, interrompe com mensagem
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error); // die() encerra a execução imediatamente
}

// Define o charset da conexão para UTF-8 completo (suporta acentos e emojis)
$conn->set_charset("utf8mb4");

// Retorna o objeto de conexão para quem incluiu este arquivo
return $conn; // Em arquivos que fazem include 'config.php', o valor retornado será atribuído à variável
?>
<?php
// api_locacao.php - Retorna os filmes alugados do cliente em JSON
header('Content-Type: application/json');
session_start();

$conn = mysqli_connect("localhost:3306", "root", "", "video_locadora");

if (!$conn) {
    echo json_encode(["erro" => "Erro na conexão: " . mysqli_connect_error()]);
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION['id_cliente'])) {
    echo json_encode([]);
    exit;
}

// Garante que o ID seja um número inteiro (segurança extra)
$id_cliente = (int) $_SESSION['id_cliente'];

// Prepara a query SQL com JOIN para pegar a imagem do filme
$stmt = $conn->prepare("
    SELECT 
        l.nome_filme, 
        l.preco_aluguel, 
        l.data_cadastro_filme, 
        l.historico_aluguel,
        f.imagem
    FROM locacao l
    LEFT JOIN filme f ON l.id_filme = f.id_filme
    WHERE l.id_cliente = ?
    ORDER BY l.data_cadastro_filme DESC
");

// Faz o bind (substitui o ? pelo valor real de $id_cliente)
$stmt->bind_param("i", $id_cliente);

// Executa a query
$stmt->execute();

// Pega o resultado da execução
$r = $stmt->get_result();

// Array para armazenar os filmes
$filmes = [];

// Pega todos os filmes e adiciona ao array
while ($row = $r->fetch_assoc()) {
    $filmes[] = $row;
}

// Fecha o statement
$stmt->close();
$conn->close();

// Retorna os filmes em formato JSON
echo json_encode($filmes);
?>

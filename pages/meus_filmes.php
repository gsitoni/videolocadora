<!-- Começo do meu HTML -->
<!DOCTYPE html> 
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aba meus filmes alugados</title>
    <link rel="stylesheet" href="../css/meus_filmes.css">
    <!--icones API-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<header id="header"><!--Começo do pescoço da página-->
    <div class="container">
        <div class="flex">
            <img class="logotipo-imagem" src="logo_site.png" alt="Logotipo do site.">
            <nav class="itens-do-menu">
                <ul>
                    <li><a href="../pages/index.html">Home</a></li>
                    <li><a href="../pages/catalogo.php">Filmes</a></li>
                    <li><a href="#">Locação</a></li>
                    <li><a href="#">Funcionários</a></li>
                    <li><a href="../pages/index.php">Cadastro</a></li>
                </ul>
            </nav><!--itens do menu-->
            <div class="barra-de-busca">
                <div class="lupa-busca">
                    <i class="bi bi-search"></i>
                </div><!--lupa buscar-->
                <div class="input-busca">
                    <input type="text" placeholder="O quê você procura?">
                </div><!--input de buscar-->
                <div class="botao-de-fechar">
                    <i class="bi bi-x-circle"></i>
                </div><!--botão de fechar-->
            </div><!--barra de buscar-->
        </div><!--flex-->
    </div><!--container-->
</header><!--Fim do pescoço da página-->
<body>
    <main>
    <h2 class="titulo-filmes">Meus filmes:</h2>
    <div id="resultados" class="carrossel-container">Carregando filmes...</div>
    </main>
</body>
</html>
<!-- Final do meu HTML -->
 <script src="../js/meus_filmes.js"></script>

<!-- Começo do meus PHP - 
 Meus filmes alugado, liga diretamente com a aba de login do usuário e o banco de dados da locação. 
-->

 <?php
// api_locacao.php
session_start();
$conn = mysqli_connect("localhost:3306", "root", "", "video_locadora");

if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Verifica se o usuário está logado
if (!isset($_SESSION['id_cliente'])) {
    echo "Você precisa estar logado para ver seus filmes alugados.";
    exit;
}

// Garante que o ID seja um número inteiro (segurança extra)
$id_cliente = (int) $_SESSION['id_cliente'];

// Prepara a query SQL com um "placeholder" (?)
$stmt = $conn->prepare("
    SELECT nome_filme, preco_aluguel, data_cadastro_filme, historico_aluguel 
    FROM locacao 
    WHERE id_cliente = ?
");

// Faz o bind (substitui o ? pelo valor real de $id_cliente)
$stmt->bind_param("i", $id_cliente);
// "i" indica que o parâmetro é do tipo inteiro

// Executa a query
$stmt->execute();

// Pega o resultado da execução
$r = $stmt->get_result();

//
if ($r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        echo "<b>" . htmlspecialchars($row["nome_filme"]) . "</b> — R$ " . 
             number_format($row["preco_aluguel"], 2, ',', '.') .
             " | Data: " . $row["data_cadastro_filme"] .
             " | Histórico: " . htmlspecialchars($row["historico_aluguel"]) . "<br>";
    }
} else {
    echo "Nenhum filme alugado encontrado.";
}

// Fecha o statement
$stmt->close();
?>
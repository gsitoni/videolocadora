<?php
// Página de Avaliações - Simples para Iniciantes
session_start();

// Conectar ao banco de dados
$conn = mysqli_connect("localhost:3306", "root", "", "video_locadora");

// Verificar se está logado
if (!isset($_SESSION['id_cliente'])) {
    header('Location: index.php');
    exit;
}

$id_cliente = $_SESSION['id_cliente'];
$nome_cliente = $_SESSION['nome_cliente'] ?? 'Cliente';

// Variável para mensagem
$mensagem = '';

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_filme = $_POST['id_filme'];
    $nota = $_POST['nota'];
    $comentario = mysqli_real_escape_string($conn, $_POST['comentario']);
    
    // Inserir ou atualizar avaliação
    $sql = "INSERT INTO avaliacao (id_cliente, id_filme, nota, comentario) 
            VALUES ($id_cliente, $id_filme, $nota, '$comentario')
            ON DUPLICATE KEY UPDATE nota = $nota, comentario = '$comentario'";
    
    if (mysqli_query($conn, $sql)) {
        $mensagem = "✅ Avaliação salva com sucesso!";
    } else {
        $mensagem = "❌ Erro: " . mysqli_error($conn);
    }
}

// Buscar filmes que o cliente alugou
$sql = "SELECT DISTINCT 
            f.id_filme, 
            f.ident_titulo, 
            f.imagem,
            a.nota,
            a.comentario
        FROM filme f
        INNER JOIN locacao l ON f.id_filme = l.id_filme
        LEFT JOIN avaliacao a ON f.id_filme = a.id_filme AND a.id_cliente = $id_cliente
        WHERE l.id_cliente = $id_cliente
        ORDER BY f.ident_titulo";

$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Avaliações</title>
    <link rel="stylesheet" href="../css/avaliacoes.css">
</head>
<body>
    <!-- Cabeçalho -->
    <header>
        <h1>⭐ Avalie seus Filmes</h1>
        <p>Olá, <strong><?php echo $nome_cliente; ?></strong>!</p>
        <a href="catalogo.php" class="btn-voltar">← Voltar para Catálogo</a>
    </header>

    <!-- Mensagem de sucesso/erro -->
    <?php if ($mensagem): ?>
        <div class="mensagem"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <!-- Lista de Filmes -->
    <main>
        <?php if (mysqli_num_rows($resultado) > 0): ?>
            <?php while ($filme = mysqli_fetch_assoc($resultado)): ?>
                <div class="card-filme">
                    <!-- Imagem do filme -->
                    <img src="../<?php echo $filme['imagem']; ?>" 
                         alt="<?php echo $filme['ident_titulo']; ?>"
                         onerror="this.src='../images/placeholder.jpg'">
                    
                    <!-- Informações e Formulário -->
                    <div class="info-filme">
                        <h2><?php echo $filme['ident_titulo']; ?></h2>
                        
                        <form method="POST">
                            <!-- Campo oculto com ID do filme -->
                            <input type="hidden" name="id_filme" value="<?php echo $filme['id_filme']; ?>">
                            
                            <!-- Seletor de Nota -->
                            <label>Sua nota (1 a 5 estrelas):</label>
                            <select name="nota" required>
                                <option value="">-- Escolha uma nota --</option>
                                <option value="1" <?php if($filme['nota'] == 1) echo 'selected'; ?>>⭐ 1 - Muito Ruim</option>
                                <option value="2" <?php if($filme['nota'] == 2) echo 'selected'; ?>>⭐⭐ 2 - Ruim</option>
                                <option value="3" <?php if($filme['nota'] == 3) echo 'selected'; ?>>⭐⭐⭐ 3 - Regular</option>
                                <option value="4" <?php if($filme['nota'] == 4) echo 'selected'; ?>>⭐⭐⭐⭐ 4 - Bom</option>
                                <option value="5" <?php if($filme['nota'] == 5) echo 'selected'; ?>>⭐⭐⭐⭐⭐ 5 - Excelente</option>
                            </select>
                            
                            <!-- Campo de Comentário -->
                            <label>Seu comentário (opcional):</label>
                            <textarea name="comentario" rows="4" placeholder="Conte o que você achou do filme..."><?php echo $filme['comentario']; ?></textarea>
                            
                            <!-- Botão Salvar -->
                            <button type="submit">💾 Salvar Avaliação</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Mensagem se não houver filmes -->
            <div class="vazio">
                <h2>🎬 Nenhum filme alugado ainda</h2>
                <p>Você precisa alugar filmes para poder avaliá-los!</p>
                <a href="catalogo.php" class="btn-catalogo">Ver Catálogo de Filmes</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

<?php
// Fechar conexão
mysqli_close($conn);
?>

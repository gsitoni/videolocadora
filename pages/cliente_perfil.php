<?php
// Versão simples do perfil: lista os filmes alugados pelo cliente logado
session_start();
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: ../pages/index.php?page=login');
    exit;
}

$conn = include '../config/config.php';

$idCliente = (int)($_SESSION['id_cliente'] ?? 0);
$nome_cliente = $_SESSION['nome_cliente'] ?? $_SESSION['usuario_logado'];

$sql = "SELECT f.ident_titulo, f.imagem, f.ident_data, l.data_cadastro_filme
    FROM locacao l
    INNER JOIN filme f ON f.id_filme = l.id_filme
    WHERE l.id_cliente = $idCliente
    ORDER BY l.id_locacao DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Cliente</title>
    <link rel="stylesheet" href="../css/cliente_perfil.css">
    </head>
<body>
    <div class="box">
    <h1>Perfil do Cliente</h1>
    <p>Olá, <strong><?php echo htmlspecialchars($nome_cliente); ?></strong></p>
    <h2>Histórico de Locações</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <ul>
            <?php while ($r = $result->fetch_assoc()): ?>
                <li style="margin-bottom:10px;">
                    <?php if (!empty($r['imagem'])): ?>
                        <img class="poster-thumb" src="<?php echo htmlspecialchars($r['imagem']); ?>" alt="Poster">
                    <?php endif; ?>
                    <?php echo htmlspecialchars($r['ident_titulo']); ?>
                    <?php if (!empty($r['ident_data'])): ?>
                        (<?php echo date('Y', strtotime($r['ident_data'])); ?>)
                    <?php endif; ?>
                    <?php if (!empty($r['data_cadastro_filme'])): ?>
                        <span class="meta-year"> • alugado em <?php echo date('d/m/Y', strtotime($r['data_cadastro_filme'])); ?></span>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Você ainda não alugou filmes.</p>
        <?php endif; ?>

        <div class="actions">
            <a href="../pages/home.php">← Voltar</a>
            <a href="../pages/catalogo.php">Catálogo</a>
        </div>
    </div>

<?php $conn->close(); ?>
</body>
</html>

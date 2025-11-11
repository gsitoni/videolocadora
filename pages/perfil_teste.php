<?php
// perfil_teste.php - Perfil do cliente com histórico e edição de dados

session_start(); // Inicia a sessão para acessar dados do usuário logado

$conn = include '../config/config.php'; // Conecta ao banco de dados

// Supondo que o id do cliente está salvo na sessão após login
$idCliente = (int)($_SESSION['id_cliente'] ?? 0);

// Se o formulário foi enviado (POST), atualiza email e telefone no banco
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoEmail = $conn->real_escape_string($_POST['email'] ?? ''); // Sanitiza email
    $novoTelefone = $conn->real_escape_string($_POST['telefone'] ?? ''); // Sanitiza telefone
    $conn->query("UPDATE cliente SET email_cliente='$novoEmail', telefone_cliente='$novoTelefone' WHERE id_cliente=$idCliente"); // Atualiza dados
}

// Busca os dados do cliente logado
$resCliente = $conn->query("SELECT nome_cliente, cpf_cliente, email_cliente, telefone_cliente, idade_cliente FROM cliente WHERE id_cliente=$idCliente");
$cliente = $resCliente ? $resCliente->fetch_assoc() : null;

// Busca o histórico de filmes alugados pelo cliente
$resFilmes = $conn->query("SELECT f.ident_titulo, f.imagem, l.data_cadastro_filme
    FROM locacao l
    INNER JOIN filme f ON f.id_filme = l.id_filme
    WHERE l.id_cliente = $idCliente
    ORDER BY l.id_locacao DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Cliente - Teste</title>
    <link rel="stylesheet" href="../css/perfil_teste.css">
</head>
<body>
    <div class="perfil-box">
        <h1>Perfil do Cliente</h1>
        <?php if ($cliente): ?>
        <!-- Formulário para editar email e telefone -->
        <form method="POST">
            <ul>
                <li><strong>Nome:</strong> <?php echo htmlspecialchars($cliente['nome_cliente']); ?></li>
                <li><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf_cliente']); ?></li>
                <li>
                    <strong>Email:</strong>
                    <!-- Campo para editar email -->
                    <input type="email" name="email" value="<?php echo htmlspecialchars($cliente['email_cliente']); ?>">
                </li>
                <li>
                    <strong>Telefone:</strong>
                    <!-- Campo para editar telefone -->
                    <input type="text" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone_cliente']); ?>">
                </li>
                <li><strong>Idade:</strong> <?php echo $cliente['idade_cliente']; ?> anos</li>
            </ul>
            <button type="submit">Salvar Alterações</button>
        </form>
        <?php else: ?>
            <p>Cliente não encontrado.</p>
        <?php endif; ?>

        <!-- Histórico de filmes alugados -->
        <h2>Histórico de Filmes Alugados</h2>
        <ul>
            <?php if ($resFilmes && $resFilmes->num_rows > 0): ?>
                <?php while ($f = $resFilmes->fetch_assoc()): ?>
                    <li>
                        <?php if (!empty($f['imagem'])): ?>
                            <!-- Mostra imagem do filme se existir -->
                            <img class="poster-thumb" src="<?php echo htmlspecialchars($f['imagem']); ?>" alt="Poster">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($f['ident_titulo']); ?>
                        <?php if (!empty($f['data_cadastro_filme'])): ?>
                            <span> • alugado em <?php echo date('d/m/Y', strtotime($f['data_cadastro_filme'])); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>Você ainda não alugou filmes.</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>

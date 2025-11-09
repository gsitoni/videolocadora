<?php
// Arquivo: cliente_perfil.php — Exibe o perfil do cliente e a lista de filmes alugados

session_start(); // Retoma sessão para usar os dados do login

// Garante que apenas usuários logados acessem esta página
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: index.php?page=login'); // Redireciona para login se não autenticado
    exit;
}

// Abre conexão com o banco (config.php retorna o objeto mysqli)
$conn = include 'config.php';

// Identifica o cliente logado (id é o mais confiável para consultas)
$idCliente = $_SESSION['id_cliente'] ?? null;

// Fallback: se por algum motivo id_cliente não estiver na sessão, tenta obter por username
if (!$idCliente) {
    $username = $conn->real_escape_string($_SESSION['usuario_logado']);
    $rsId = $conn->query("SELECT id_cliente FROM cliente WHERE username = '$username' LIMIT 1");
    if ($rsId && $rsId->num_rows > 0) {
        $idCliente = (int)$rsId->fetch_assoc()['id_cliente'];
        $_SESSION['id_cliente'] = $idCliente; // atualiza sessão para próximas vezes
    }
}

// Se ainda assim não obteve o id, aborta com mensagem amigável
if (!$idCliente) {
    echo '<p>Não foi possível identificar o cliente logado.</p>';
    exit;
}

// Consulta simples: locações do cliente com dados básicos do filme
// Observação: Ajuste conforme sua modelagem/fluxo real de locação
$sql = "
    SELECT 
        l.id_locacao,
        l.id_filme,
        f.ident_titulo,
        f.imagem,
        f.ident_genero,
        f.ident_midia,
        f.ident_class_indic,
        f.ident_data
    FROM locacao l
    INNER JOIN filme f ON f.id_filme = l.id_filme
    WHERE l.id_cliente = $idCliente
    ORDER BY l.id_locacao DESC
";

$result = $conn->query($sql);

$nome_cliente = $_SESSION['nome_cliente'] ?? $_SESSION['usuario_logado'];
$is_admin = $_SESSION['is_admin'] ?? false;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Cliente - Clube da Fita</title>
    <link rel="stylesheet" href="locadora.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .perfil-container { max-width: 1000px; margin: 24px auto; padding: 16px; }
        .perfil-header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 16px; }
        .perfil-user { display:flex; align-items:center; gap: 12px; }
        .avatar { width: 44px; height: 44px; border-radius: 50%; background:#bba07a; color:#1a1209; display:flex; align-items:center; justify-content:center; font-weight:700; }
        .alugueis-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:16px; }
        .aluguel-card { background:#20160b; border:1px solid rgba(255,255,255,0.08); border-radius:10px; overflow:hidden; }
        .aluguel-poster { width:100%; aspect-ratio: 2/3; background:#2a1c0f; display:flex; align-items:center; justify-content:center; }
        .aluguel-poster img { width:100%; height:100%; object-fit:cover; display:block; }
        .aluguel-info { padding:12px; }
        .aluguel-info h3 { margin:0 0 6px; font-size:16px; }
        .aluguel-meta { font-size:12px; opacity:.85; display:flex; gap:8px; flex-wrap:wrap; }
        .badge { display:inline-block; padding:2px 6px; border-radius:6px; background:#3a2a17; font-size:11px; }
        .empty { padding:24px; text-align:center; opacity:.85; }
        .nav-actions { display:flex; gap:8px; }
        .btn-link { color:#f2e5d5; text-decoration:none; border:1px solid rgba(255,255,255,.15); padding:8px 12px; border-radius:8px; }
        .btn-link:hover { background:rgba(255,255,255,.06); }
    </style>
</head>
<body>
    <header class="header-locadora">
        <div class="container-header">
            <div class="logo-area">
                <img src="logo_site.png" alt="Logo Clube da Fita" class="logo-locadora">
                <h1>Clube da Fita</h1>
            </div>
            <nav class="nav-locadora">
                <span class="usuario-info">👤 <?php echo htmlspecialchars($nome_cliente); ?></span>
                <?php if ($is_admin): ?><span class="badge-admin-nav">ADMIN</span><?php endif; ?>
                <a href="home.php" class="btn-voltar-home">← Home</a>
                <a href="locadora.php" class="btn-voltar-home">🎬 Catálogo</a>
            </nav>
        </div>
    </header>

    <main class="perfil-container">
        <div class="perfil-header">
            <div class="perfil-user">
                <div class="avatar"><?php echo strtoupper(substr($nome_cliente, 0, 2)); ?></div>
                <div>
                    <h2 style="margin:0;">Seu Perfil</h2>
                    <div style="opacity:.8; font-size:14px;">Filmes que você já alugou</div>
                </div>
            </div>
            <div class="nav-actions">
                <a class="btn-link" href="index.php">Área de Login</a>
                <a class="btn-link" href="index.php?page=logout" onclick="return confirm('Deseja sair?')">Sair</a>
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="alugueis-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="aluguel-card">
                        <div class="aluguel-poster">
                            <img src="<?php echo htmlspecialchars($row['imagem']); ?>" alt="Poster de <?php echo htmlspecialchars($row['ident_titulo']); ?>">
                        </div>
                        <div class="aluguel-info">
                            <h3><?php echo htmlspecialchars($row['ident_titulo']); ?></h3>
                            <div class="aluguel-meta">
                                <span class="badge"><?php echo htmlspecialchars($row['ident_genero']); ?></span>
                                <span class="badge"><?php echo htmlspecialchars($row['ident_midia']); ?></span>
                                <span class="badge"><?php echo htmlspecialchars($row['ident_class_indic']); ?></span>
                                <?php if (!empty($row['ident_data'])): ?>
                                    <span><?php echo date('Y', strtotime($row['ident_data'])); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <p>Você ainda não possui filmes alugados.</p>
                <p><a class="btn-link" href="locadora.php">Explorar Catálogo</a></p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer-locadora">
        <div class="footer-content">
            <div class="footer-col">
                <h4>Clube da Fita</h4>
                <p>Sua locadora de filmes clássicos online</p>
            </div>
            <div class="footer-col">
                <h4>Navegação</h4>
                <a href="home.php">Dashboard</a>
                <a href="locadora.php">Catálogo</a>
            </div>
            <div class="footer-col">
                <h4>Contato</h4>
                <p>📧 contato@clubedafita.com</p>
                <p>📱 (41) 9999-9999</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Clube da Fita - Todos os direitos reservados</p>
        </div>
    </footer>
</body>
</html>
<?php $conn->close(); // Fecha a conexão ?>

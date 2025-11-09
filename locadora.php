<?php

//CATÁLOGO DE FILMES

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: index.php?page=login');
    exit;
}

// Inclui no banco de dados
$conn = include 'config.php';

// Buscar filmes disponíveis
$query = "SELECT * FROM filme ORDER BY ident_titulo";
$resultado = $conn->query($query);

$usuario_logado = $_SESSION['usuario_logado'];
$nome_cliente = $_SESSION['nome_cliente'] ?? $usuario_logado;
$is_admin = $_SESSION['is_admin'] ?? false;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clube da Fita - Locadora</title>
    <link rel="stylesheet" href="locadora.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header-locadora">
        <div class="container-header">
            <div class="logo-area">
                <img src="logo_site.png" alt="Logo Clube da Fita" class="logo-locadora">
                <h1>Clube da Fita</h1>
            </div>
            <nav class="nav-locadora">
                <span class="usuario-info">👤 <?php echo htmlspecialchars($nome_cliente); ?></span>
                <?php if ($is_admin): ?>
                    <span class="badge-admin-nav">ADMIN</span>
                <?php endif; ?>
                <a href="home.php" class="btn-voltar-home">← Voltar</a>
            </nav>
        </div>
    </header>

    <!-- Banner -->
    <section class="banner-locadora">
        <div class="banner-content">
            <h2>🎬 Bem-vindo à Locadora!</h2>
            <p>Explore nosso catálogo de filmes clássicos</p>
        </div>
    </section>

    <!-- Catálogo de Filmes -->
    <section class="catalogo">
        <div class="container-catalogo">
            <h2>Filmes Disponíveis</h2>
            
            <div class="filmes-grid">
                <?php
                if ($resultado && $resultado->num_rows > 0) {
                    while ($filme = $resultado->fetch_assoc()) {
                        ?>
                        <div class="filme-card">
                            <div class="filme-poster">
                                <div class="poster-placeholder">
                                    🎥
                                </div>
                            </div>
                            <div class="filme-info">
                                <h3><?php echo htmlspecialchars($filme['ident_titulo']); ?></h3>
                                <p class="filme-genero">
                                    <i class="bi bi-tag"></i> 
                                    <?php echo htmlspecialchars($filme['ident_genero']); ?>
                                </p>
                                <p class="filme-ano">
                                    <i class="bi bi-calendar"></i> 
                                    <?php echo date('Y', strtotime($filme['ident_data'])); ?>
                                </p>
                                <p class="filme-duracao">
                                    <i class="bi bi-clock"></i> 
                                    <?php echo $filme['ident_duracao']; ?>
                                </p>
                                <p class="filme-diretor">
                                    <i class="bi bi-person"></i> 
                                    <?php echo htmlspecialchars($filme['ident_nome_diretor']); ?>
                                </p>
                                <div class="filme-classificacao">
                                    <span class="badge-classificacao">
                                        <?php echo htmlspecialchars($filme['ident_class_indic']); ?>
                                    </span>
                                    <span class="badge-midia">
                                        <?php echo htmlspecialchars($filme['ident_midia']); ?>
                                    </span>
                                </div>
                                <p class="filme-sinopse">
                                    <?php echo htmlspecialchars($filme['ident_sinopse']); ?>
                                </p>
                                <div class="filme-elenco">
                                    <strong>Elenco:</strong>
                                    <p><?php echo htmlspecialchars($filme['ident_elenco']); ?></p>
                                </div>
                                <div class="filme-status">
                                    <span class="badge-estado estado-<?php echo $filme['estado_filme']; ?>">
                                        Estado: <?php echo $filme['estado_filme']; ?>/10
                                    </span>
                                </div>
                                <button class="btn-alugar" onclick="alugarFilme(<?php echo $filme['id_filme']; ?>, '<?php echo htmlspecialchars($filme['ident_titulo']); ?>')">
                                    <i class="bi bi-cart-plus"></i> Alugar Filme
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="sem-filmes">Nenhum filme disponível no momento.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-locadora">
        <div class="footer-content">
            <div class="footer-col">
                <h4>Clube da Fita</h4>
                <p>Sua locadora de filmes clássicos online</p>
            </div>
            <div class="footer-col">
                <h4>Links Rápidos</h4>
                <a href="home.php">Dashboard</a>
                <?php if ($is_admin): ?>
                    <a href="index.php?page=usuarios">Clientes</a>
                <?php endif; ?>
            </div>
            <div class="footer-col">
                <h4>Contato</h4>
                <p>📧 contato@clubedafita.com</p>
                <p>📱 (41) 9999-9999</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Clube da Fita - Todos os direitos reservados</p>
        </div>
    </footer>

    <script>
        function alugarFilme(idFilme, tituloFilme) {
            if (confirm(`Deseja alugar o filme "${tituloFilme}"?`)) {
                alert(`Filme "${tituloFilme}" alugado com sucesso! 🎉\n\nID do Filme: ${idFilme}`);
                
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
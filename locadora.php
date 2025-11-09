<?php
// Arquivo: locadora.php — renderiza o catálogo de filmes para usuários autenticados

session_start(); // Inicia a sessão PHP para persistir dados entre requisições (ex.: login)

// Verificar se o usuário está logado (protege a rota)
if (!isset($_SESSION['usuario_logado'])) { // Se não existe a chave 'usuario_logado' na sessão
    header('Location: index.php?page=login'); // Redireciona para a página de login (deve ser chamado antes de enviar HTML)
    exit; // Interrompe a execução para garantir que nada mais será processado após o redirect
}

// Abre conexão com o banco de dados; config.php retorna um objeto mysqli em $conn
$conn = include 'config.php'; // include carrega e executa config.php; o return desse arquivo vira o valor de $conn

// Monta a consulta para buscar todos os filmes ordenados por título
$query = "SELECT * FROM filme ORDER BY ident_titulo"; // string SQL simples sem parâmetros (apenas leitura)
$resultado = $conn->query($query); // Executa a query no MySQL e retorna um mysqli_result ou false

// Captura informações do usuário logado a partir da sessão
$usuario_logado = $_SESSION['usuario_logado']; // Username usado como fallback para exibição
$nome_cliente = $_SESSION['nome_cliente'] ?? $usuario_logado; // Usa nome amigável se existir; senão, usa o username
$is_admin = $_SESSION['is_admin'] ?? false; // Flag booleana indicando privilégios de administrador
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
                <a href="cliente_perfil.php" class="btn-voltar-home">Perfil</a>
            </nav>
        </div>
    </header>

    <!-- Banner -->
    <section class="banner-locadora">
        <div class="banner-content">
            <h2>🎬 Bem-vindo à Locadora!</h2>
            <p>Explore nosso catálogo de filmes </p>
        </div>
    </section>

    <!-- Catálogo de Filmes -->
    <section class="catalogo">
        <div class="container-catalogo">
            <h2>Filmes Disponíveis</h2>
            
            <div class="filmes-grid">
                <?php
                // Inicia o bloco PHP responsável por renderizar a lista de filmes
                if ($resultado && $resultado->num_rows > 0) { // Verifica se a consulta foi bem-sucedida e retornou linhas
                    while ($filme = $resultado->fetch_assoc()) { // Itera sobre cada linha do resultado como array associativo
                        // Debug somente para desenvolvimento: imprime todos os campos do filme atual
                        // Atenção: var_dump gera saída direta na página e pode quebrar o layout; remova em produção
                        // var_dump($filme);
                        ?>
                        <div class="filme-card">
                            <div class="filme-poster">
                                <div class="poster-placeholder">
                                    <img src="<?= htmlspecialchars($filme['imagem']) ?>" alt="<?php echo htmlspecialchars($filme['ident_titulo']); ?>">
                                </div>
                            </div>
                            <div class="filme-info">
                                <h3><?php echo htmlspecialchars($filme['ident_titulo']); ?></h3> <!-- Exibe o título do filme, escapando HTML -->
                                <p class="filme-genero">
                                    <i class="bi bi-tag"></i> 
                                    <?php echo htmlspecialchars($filme['ident_genero']); ?> <!-- Exibe o gênero do filme -->
                                </p>
                                <p class="filme-ano">
                                    <i class="bi bi-calendar"></i> 
                                    <?php echo date('Y', strtotime($filme['ident_data'])); ?> <!-- Converte a data completa para apenas o ano -->
                                </p>
                                <p class="filme-duracao">
                                    <i class="bi bi-clock"></i> 
                                    <?php echo $filme['ident_duracao']; ?> <!-- Exibe a duração no formato HH:MM:SS -->
                                </p>
                                <p class="filme-diretor">
                                    <i class="bi bi-person"></i> 
                                    <?php echo htmlspecialchars($filme['ident_nome_diretor']); ?> <!-- Exibe o nome do diretor -->
                                </p>
                                <div class="filme-classificacao">
                                    <span class="badge-classificacao">
                                        <?php echo htmlspecialchars($filme['ident_class_indic']); ?> <!-- Selo de classificação indicativa -->
                                    </span>
                                    <span class="badge-midia">
                                        <?php echo htmlspecialchars($filme['ident_midia']); ?> <!-- Tipo de mídia (DVD/Blu-ray/Digital) -->
                                    </span>
                                </div>
                                <p class="filme-sinopse">
                                    <?php echo htmlspecialchars($filme['ident_sinopse']); ?> <!-- Sinopse curta do filme -->
                                </p>
                                <div class="filme-elenco">
                                    <strong>Elenco:</strong>
                                    <p><?php echo htmlspecialchars($filme['ident_elenco']); ?></p> <!-- Lista resumida de atores principais -->
                                </div>
                                <div class="filme-status">
                                    <span class="badge-estado estado-<?php echo $filme['estado_filme']; ?>">
                                        Estado: <?php echo $filme['estado_filme']; ?>/10 <!-- Nota/estado do filme para catálogo -->
                                    </span>
                                </div>
                                <button class="btn-alugar" onclick="alugarFilme(<?php echo $filme['id_filme']; ?>, '<?php echo htmlspecialchars($filme['ident_titulo']); ?>')">
                                    <i class="bi bi-cart-plus"></i> Alugar Filme
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else { // Caso não haja filmes na base, mostra mensagem vazia
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
                <a href="cliente_perfil.php">Perfil do Cliente</a>
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
                window.location.href = `alugar.php?id=${idFilme}`;
            }
        }
    </script>
</body>
</html>
<?php
$conn->close(); // Encerra a conexão com o banco para liberar recursos
?>
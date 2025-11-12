<?php
// Arquivo: home.php
// Objetivo: Exibir dashboard principal após login com informações básicas e navegação.
// Observação: Esta página assume que o usuário já realizou login e mantém dados na sessão.

session_start(); // Inicia/retoma a sessão para acessar variáveis de controle de autenticação.

// Autorização: se não há usuário logado na sessão, redireciona para página inicial (login/cadastro).
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: ../pages/index.php'); // Redireciona antes de qualquer saída HTML.
    exit; // Interrompe execução para evitar que o restante do conteúdo seja enviado.
}

// Variáveis de contexto do usuário — usadas para personalizar a interface.
$usuario_logado = $_SESSION['usuario_logado']; // Username armazenado na sessão.
$nome_cliente = $_SESSION['nome_cliente'] ?? $usuario_logado; // Nome real; fallback para username se não definido.
$is_admin = $_SESSION['is_admin'] ?? false; // Booleano indicando privilégios administrativos.

// (Opcional) Debug da sessão — descomente para inspecionar valores durante desenvolvimento.
// echo "<pre>DEBUG SESSION:\n"; // Início de bloco formatado.
// var_dump([
//     'usuario_logado' => $usuario_logado,
//     'nome_cliente' => $nome_cliente,
//     'is_admin' => $is_admin,
// ]); // Exibe estrutura das variáveis-chave.
// echo "</pre>"; // Fim do bloco.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clube da Fita</title>
     <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
    </style>
</head>

<header id="header">
    <div class="container">
        <div class="flex">
            <img class="logotipo-imagem" src="../img/logo_site.png" alt="Logotipo do site mostrando uma imagem de uma fita cassete.">
            <nav class="itens-do-menu">
                <ul>
                    <li><a href="../pages/home.php">Home</a></li>
                    <li><a href="#filmes-section">Filmes</a></li>
                    <li><a href="../pages/catalogo.php">Catálogo</a></li>
                    <li><a href="../pages/cliente_perfil.php">Perfil</a></li>
                    <li><a href="../pages/funcionarios.php">Funcionários</a></li>
                    <?php if ($is_admin): ?>
                    <li><a href="../pages/index.php?page=usuarios">Clientes</a></li>
                    <?php endif; ?>
                    <li class="sair"><a href="../pages/index.php" onclick="return confirm('Deseja realmente sair?')">Sair</a></li>
                </ul>
            </nav>
            <div class="barra-de-busca">
                <div class="lupa-busca">
                    <i class="bi bi-search"></i>
                </div>
                <div class="input-busca">
                    <input type="text" placeholder="O quê você procura?">
                </div>
            </div>
        </div>
    </div>
</header>

<body>
    <!-- User Info Badge -->
    <div class="user-badge">
        <i class="bi bi-person-circle"></i>
        <span>Olá, <strong><?php echo htmlspecialchars($nome_cliente); ?></strong>!</span> <!-- Saudação personalizada usando nome ou username -->
        <?php if ($is_admin): ?> <!-- Exibe badge ADMIN apenas para usuários com is_admin=true -->
            <span class="admin-tag">ADMIN</span>
        <?php endif; ?>
    </div>

    <section style="background-image: url(../img/download5.jpg);" class="banner">
        <div class="banner-overlay">
        </div>
    </section>

    <!-- Cards de Ações Rápidas -->
    <section class="acoes-rapidas">
        <div class="container-acoes">
            <a href="#equipe-section" class="card-acao">
                <h3>Sobre Nós</h3>
                <p>Conheça nossa equipe</p>
            </a>
        </div>
    </section>

    <!-- Filmes -->
    <section class="filmes" id="filmes-section">
        <h2>Destaques da Semana</h2>
        <div class="filmes-container" id="carrossel">
            <div class="card-filme">
                <img class="img-poster" src="../img/poster-1.jpg" alt="Poster do filme">
                <h3>Nome do filme</h3>
                <p>Descrição simples do filme...</p>
            </div>
            <div class="card-filme">
                <img class="img-poster" src="../img/poster-2.jpg" alt="Poster do filme">
                <h3>Nome do filme</h3>
                <p>Descrição simples do filme...</p>
            </div>
            <div class="card-filme">
                <img class="img-poster" src="../img/poster-3.jpg" alt="Poster do filme">
                <h3>Nome do filme</h3>
                <p>Descrição simples do filme...</p>
            </div>
            <div class="card-filme">
                <img class="img-poster" src="../img/poster-4.jpg" alt="Poster do filme">
                <h3>Nome do filme</h3>
                <p>Descrição simples do filme...</p>
            </div>
        </div>
        <div class="navegacao">
            <button onclick="rolar(-220)">◀</button>
            <button onclick="rolar(220)">▶</button>
        </div>
    </section>

    <!-- Avisos -->
    <section class="avisos">
        <div>
            <h3>Avisos importantes</h3>
            <p>Bem-vindo ao sistema! Explore nosso catálogo de filmes clássicos.</p>
        </div>
        <div class="data">
            <?php echo date('d'); ?><br><?php echo strtoupper(date('M')); ?> <!-- Dia e mês atuais (em maiúsculas) -->
        </div>
    </section>

    <!-- Equipe -->
    <div class="titulo-equipe" id="equipe-section">
        <span>Criadores e colaboradores do site:</span>
    </div>
    <section class="equipe">
       <section class="equipe">
        <div class="equipe-container">
            <div class="membro">
                <div class="foto"></div>
                <h4>Pedro</h4>
                <p>Um pouco sobre Pedro...</p>
            </div>
            <div class="membro">
                <div class="foto"></div>
                <h4>Gaby</h4>
                <p>Um pouco sobre Gaby...</p>
            </div>
            <div class="membro">
                <div class="foto"></div>
                <h4>Giulia</h4>
                <p>Um pouco sobre Giulia...</p>
            </div>
            <div class="membro">
                <div class="foto"></div>
                <h4>Thiago</h4>
                <p>Um pouco sobre Thiago...</p>
            </div>
            <div class="membro">
                <div class="foto"></div>
                <h4>Tomás</h4>
                <p>Um pouco sobre Tomás...</p>
            </div>
        </div>
    </section>


    <!-- Rodapé -->
    <footer>
        <div>
            <h4>Clube da fita:</h4>
            <p>Sua locadora de filmes clássicos online desde 2025.</p>
            <p>Preservando a nostalgia do cinema.</p>
        </div>
        <div>
            <h4>Links Rápidos:</h4>
            <p><a href="../pages/home.php">Dashboard</a></p>
            <p><a href="../pages/catalogo.php">Catálogo</a></p>
            <p><a href="../pages/cliente_perfil.php">Perfil do Cliente</a></p>
            <p><a href="../pages/index.html">Página Inicial</a></p>
        </div>
        <div>
            <h4>Contato:</h4>
            <p>📧 contato@clubedafita.com</p>
            <p>📱 (41) 9999-9999</p>
            <p>📍 São José dos Pinhais - PR</p>
        </div>
    </footer>

<script src="../js/main.js"></script>
</body>
</html>
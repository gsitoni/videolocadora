<?php
// Arquivo: index.php
// Objetivo: Roteia entre telas (home, cadastro, login, usuários) e processa formulários básicos.
// Nota: Este script centraliza navegação baseada em 'page' (query string) e manipula sessão.

session_start(); // Inicia sessão para armazenar dados de autenticação e estado do usuário.

$page = $_GET['page'] ?? 'home'; // Página alvo; se não vier definida, cairá em 'home'.

// Conectar ao banco de dados: include retorna o objeto mysqli definido em config.php.
$conn = require_once __DIR__ . '/../config/config.php'; // returns mysqli connection in $conn

$mensagem = ""; // Armazena feedback (sucesso/erro) a ser exibido ao usuário.
$acesso_negado = false; // Flag para bloquear exibição de conteúdo administrativo.

// Regra de acesso: se tentar acessar a página 'usuarios', valida login e privilégio admin.
if ($page === 'usuarios') {
    $usuario_logado = $_SESSION['usuario_logado'] ?? null; // Obtém usuário logado (ou null se ausente).
    
    if (!$usuario_logado) { // Caso não esteja autenticado.
        $mensagem = "<div id='mensagem' class='mensagem error'>Você precisa fazer login para acessar esta página!</div>";
        $acesso_negado = true; // Impede renderização da lista de usuários.
    } elseif (!($_SESSION['is_admin'] ?? false)) { // Autenticado mas sem privilégio admin.
        $mensagem = "<div id='mensagem' class='mensagem error'>Acesso negado! Apenas administradores podem ver a lista de usuários.</div>";
        $acesso_negado = true;
    }
}

// Processamento de formulários — verifica se requisição é POST e age conforme a página.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Fluxo de cadastro de novo cliente.
    if ($page === 'cadastro') {
        // Coleta e normaliza valores do formulário
        $nome_cliente     = trim($_POST['nome_cliente'] ?? '');
        $cpf_cliente      = trim($_POST['cpf_cliente'] ?? '');
        $idade_cliente    = (int)($_POST['idade_cliente'] ?? 0);
        $telefone_cliente = trim($_POST['telefone_cliente'] ?? '');
        $email_cliente    = trim($_POST['email_cliente'] ?? '');
        $username         = trim($_POST['username'] ?? '');
        $password         = $_POST['password'] ?? '';

        // Gera hash seguro usando password_hash (bcrypt/argon conforme disponível)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        
        // Debug opcional do POST — descomentar para inspecionar dados recebidos.
        // echo "<pre>DEBUG POST: "; var_dump($_POST); echo "</pre>";

        // Validação mínima: todos os campos precisam estar preenchidos.
        if (!empty($nome_cliente) && !empty($cpf_cliente) && !empty($idade_cliente) && 
            !empty($telefone_cliente) && !empty($email_cliente) && !empty($username) && !empty($password)) {
            
            // Verifica se já existe o mesmo username ou CPF usando prepared statement
            $check_stmt = $conn->prepare("SELECT id_cliente FROM cliente WHERE username = ? OR cpf_cliente = ?");
            $check_stmt->bind_param('ss', $username, $cpf_cliente);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) { // Já cadastrado.
                $mensagem = "<div id='mensagem' class='mensagem error'>Usuário ou CPF já cadastrado!</div>";
            } else {
                // Insere novo cliente com prepared statement e password_hash
                $insert_stmt = $conn->prepare("INSERT INTO cliente (nome_cliente, cpf_cliente, idade_cliente, telefone_cliente, email_cliente, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($insert_stmt) {
                    $insert_stmt->bind_param('ssissss', $nome_cliente, $cpf_cliente, $idade_cliente, $telefone_cliente, $email_cliente, $username, $password_hash);
                    if ($insert_stmt->execute()) {
                        $mensagem = "<div id='mensagem' class='mensagem success'>Cliente cadastrado com sucesso!</div>";
                    } else {
                        $mensagem = "<div id='mensagem' class='mensagem error'>Erro ao cadastrar: " . htmlspecialchars($insert_stmt->error) . "</div>";
                    }
                    $insert_stmt->close();
                } else {
                    $mensagem = "<div id='mensagem' class='mensagem error'>Erro interno ao preparar cadastro.</div>";
                }
            }
            $check_stmt->close();
        } else { // Falha de preenchimento.
            $mensagem = "<div id='mensagem' class='mensagem error'>Preencha todos os campos!</div>";
        }
        
    // Fluxo de login.
    } elseif ($page === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($username) && $password !== '') { // Validação mínima.
            $stmt = $conn->prepare("SELECT * FROM cliente WHERE username = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $cliente = $result->fetch_assoc();
                    $stored = $cliente['password'];

                    $authenticated = false;

                    // 1) Se senha armazenada for um hash compatível com password_verify
                    if (is_string($stored) && strlen($stored) > 0 && $stored[0] === '$' && password_verify($password, $stored)) {
                        $authenticated = true;
                    }

                    // 2) Suporte a senhas legadas em SHA-256: compara e migra para password_hash
                    if (!$authenticated && $stored === hash('sha256', $password)) {
                        $authenticated = true;
                        // Migra a senha para password_hash
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $conn->prepare("UPDATE cliente SET password = ? WHERE id_cliente = ?");
                        if ($upd) {
                            $upd->bind_param('si', $newHash, $cliente['id_cliente']);
                            $upd->execute();
                            $upd->close();
                        }
                    }

                    // 3) Suporte a senhas legadas em texto claro: compara e migra para password_hash
                    if (!$authenticated && $stored === $password) {
                        $authenticated = true;
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $conn->prepare("UPDATE cliente SET password = ? WHERE id_cliente = ?");
                        if ($upd) {
                            $upd->bind_param('si', $newHash, $cliente['id_cliente']);
                            $upd->execute();
                            $upd->close();
                        }
                    }

                    if ($authenticated) {
                        // Armazena dados principais na sessão para uso em outras páginas.
                        $_SESSION['usuario_logado'] = $cliente['username'];
                        $_SESSION['nome_cliente']   = $cliente['nome_cliente'];
                        $_SESSION['is_admin']       = (bool)$cliente['is_admin']; // Normaliza para booleano real.
                        $_SESSION['id_cliente']     = $cliente['id_cliente'];
                        $stmt->close();
                        header('Location: home.php'); // Redireciona para dashboard.
                        exit; // Encerrar execução para evitar renderização da tela de login.
                    } else {
                        $mensagem = "<div id='mensagem' class='mensagem error'>Senha incorreta!</div>";
                    }
                } else {
                    $mensagem = "<div id='mensagem' class='mensagem error'>Usuário não encontrado!</div>";
                }

                $stmt->close();
            } else {
                $mensagem = "<div id='mensagem' class='mensagem error'>Erro interno ao preparar consulta de login.</div>";
            }
        } else { // Campos vazios.
            $mensagem = "<div id='mensagem' class='mensagem error'>Preencha todos os campos!</div>";
        }
        
    // Fluxo de logout.
    } elseif ($page === 'logout') {
        session_destroy(); // Remove todos os dados da sessão (deslogar).
        header('Location: ../pages/index.html'); // Redireciona para página estática inicial.
        exit; // Garante término da execução.
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($page); ?> - Sistema Clube da Fita</title> <!-- Título dinâmico com capitalização da página -->
    <link rel="stylesheet" href="../css/index.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mensagem = document.getElementById('mensagem');
            if (mensagem) {
                setTimeout(() => {
                    mensagem.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</head>

<body class="<?php echo $page; ?>"> <!-- Define classe do body igual ao nome da página para estilização contextual -->
    
    <?php echo $mensagem; ?> <!-- Feedback ao usuário (erros/sucesso) -->

    <?php if ($page === 'home'): ?>
        <div class="home-container">
            
            <h1>Clube da Fita</h1>
            
            <?php if (isset($_SESSION['usuario_logado'])): ?> <!-- Bloco para usuários autenticados -->
                <div class="usuario-info-box">
                    <p>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['nome_cliente'] ?? $_SESSION['usuario_logado']); ?></strong>!</p>
                    <?php if ($_SESSION['is_admin']): ?> <!-- Exibe badge se perfil for administrador -->
                        <span class="badge-admin">ADMINISTRADOR</span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>Bem-vindo! Escolha uma opção:</p>
            <?php endif; ?>
            
            <div class="buttons-container">
                <?php if (isset($_SESSION['usuario_logado'])): ?> <!-- Se logado, mostra atalho para página inicial -->
                    <a href="../pages/home.php" class="btn-link btn-destaque">
                        <span class="btn-icon">🎬</span>
                        Ir para Página Inicial
                    </a>
                <?php endif; ?>
                
                <?php if (!isset($_SESSION['usuario_logado'])): ?> <!-- Visitante: mostra botões cadastro/login -->
                    <a href="../pages/index.php?page=cadastro" class="btn-link">Fazer Cadastro</a>
                    <a href="../pages/index.php?page=login" class="btn-link">Fazer Login</a>
                <?php else: ?>
                    <?php if ($_SESSION['is_admin']): ?> <!-- Link extra para lista de usuários se admin -->
                        <a href="../pages/index.php?page=usuarios" class="btn-link">Ver Clientes</a> <!-- Navega para área administrativa -->
                    <?php endif; ?>
                    <form method="POST" action="../pages/index.php?page=logout" style="width: 100%;">
                        <button type="submit" class="btn-link" style="width: 100%; border: none; cursor: pointer;">Sair</button> <!-- Botão de logout usando formulário POST -->
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="info-box">
                <p>Nós somos o clube da fita.</p>
                <p>Aqui você acha o filme que precisa e muito mais.</p>
            </div>
        </div>

    <?php elseif ($page === 'cadastro'): ?> <!-- Formulário de cadastro de novo cliente -->
        <form id="form-cadastro" class="form-container form-cadastro-completo" action="../pages/index.php?page=cadastro" method="POST">
  
            <fieldset>
                <legend>Cadastro de Cliente</legend>
                <div class="form-box">
                    <input type="text" 
                           id="nome_cliente" 
                           name="nome_cliente" 
                           placeholder="Nome completo" 
                           required
                           minlength="3">
                           
                    <input type="text" 
                           id="cpf_cliente" 
                           name="cpf_cliente" 
                           placeholder="CPF (somente números)" 
                           required
                           pattern="[0-9]{11}"
                           maxlength="11">
                           
                    <input type="number" 
                           id="idade_cliente" 
                           name="idade_cliente" 
                           placeholder="Idade" 
                           required
                           min="1"
                           max="150">
                           
                    <input type="tel" 
                           id="telefone_cliente" 
                           name="telefone_cliente" 
                           placeholder="Telefone: (00)00000-0000" 
                           required>
                           
                    <input type="email" 
                           id="email_cliente" 
                           name="email_cliente" 
                           placeholder="E-mail" 
                           required>
                    
                    <hr style="margin: 20px 0; border: 1px solid rgba(139, 116, 78, 0.3);">
                    
                    <p style="color: #d4c5b0; font-size: 14px; margin-bottom: 10px;">Dados de Acesso:</p>
                    
                    <input type="text" 
                           id="username" 
                           name="username" 
                           placeholder="Escolha um nome de usuário" 
                           required
                           minlength="3"
                           maxlength="20">
                           
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Crie uma senha" 
                           required
                           minlength="4">
                           
                    <button type="submit" class="btn-cadastro">Cadastrar Cliente</button>
                    
                    <p class="link-nav">
                        Já tem conta? <a href="../pages/index.php?page=login">Fazer Login</a>
                    </p>
                    
                    <a href="../pages/index.php" class="btn-home">← Área de Login</a>
                    <a href="../pages/index.html" class="btn-home-site">← Voltar ao Site</a>
                </div>
            </fieldset>
        </form>
        
    <?php elseif ($page === 'login'): ?> <!-- Formulário de login -->
        <form id="form-login" class="form-container" action="../pages/index.php?page=login" method="POST">
            
            <fieldset>
                <legend>Login</legend>
                <div class="form-box">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           placeholder="Digite seu usuário" 
                           required>
                           
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Digite sua senha" 
                           required>
                           
                    <button type="submit" class="btn-login">Entrar</button>
                    
                    <p class="link-nav">
                        Ainda não tem conta? <a href="index.php?page=cadastro">Cadastre-se aqui</a>
                    </p>
                    
                    <a href="../pages/index.php" class="btn-home">← Área de Login</a>
                    <a href="../pages/index.html" class="btn-home-site">← Voltar ao Site</a>
                </div>
            </fieldset>
        </form>
        
    <?php elseif ($page === 'usuarios'): ?> <!-- Página administrativa: listagem de clientes -->
    <?php if ($acesso_negado): ?> <!-- Renderiza aviso de acesso negado se flag estiver ativa -->
            <div class="home-container">
                <h1>Acesso Negado</h1>
                <div class="acesso-negado-box">
                    <p>🚫</p>
                    <p>Você não tem permissão para acessar esta página.</p>
                    <p>Apenas administradores podem visualizar a lista de clientes.</p>
                </div>
                <div class="buttons-container">
                    <a href="index.php" class="btn-link">← Voltar ao Início</a>
                    <?php if (!isset($_SESSION['usuario_logado'])): ?>
                        <a href="../pages/index.php?page=login" class="btn-link">Fazer Login</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="usuarios-container">
                <div class="usuarios-header">
                    <h1>Clientes Cadastrados</h1>
                    <p>Lista de todos os clientes do sistema</p>
                    <span class="badge-admin-header">Área Administrativa</span>
                </div>
                
                <?php 
                $clientes_query = "SELECT * FROM cliente ORDER BY data_cadastro DESC"; // Busca clientes ordenados pelo cadastro mais recente
                $clientes_result = $conn->query($clientes_query); // Executa consulta de listagem de clientes
                
                if ($clientes_result->num_rows == 0): ?>
                    <div class="usuarios-vazio">
                        <p>Nenhum cliente cadastrado ainda.</p>
                        <a href="../pages/index.php?page=cadastro" class="btn-link">Fazer primeiro cadastro</a>
                    </div>
                <?php else: ?>
                    <div class="usuarios-stats">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $clientes_result->num_rows; ?></span>
                            <span class="stat-label">Total de Clientes</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?php echo date('d/m/Y'); ?></span>
                            <span class="stat-label">Data Atual</span>
                        </div>
                    </div>
                    
                    <div class="usuarios-lista">
                        <?php 
                        $index = 0;
                        while ($cliente = $clientes_result->fetch_assoc()):  // Itera cada cliente retornado como array associativo
                            $index++;
                        ?>
                            <div class="usuario-card cliente-card">
                                <div class="usuario-avatar">
                                    <?php echo strtoupper(substr($cliente['nome_cliente'], 0, 2)); ?> <!-- Avatar com iniciais do nome -->
                                </div>
                                <div class="usuario-info">
                                    <h3>
                                        <?php echo htmlspecialchars($cliente['nome_cliente']); ?>
                                        <?php if ($cliente['is_admin']): ?> <!-- Marca admin diretamente no card -->
                                            <span class="badge-mini-admin">ADMIN</span>
                                        <?php endif; ?>
                                    </h3>
                                    <p><strong>Usuário:</strong> <?php echo htmlspecialchars($cliente['username']); ?></p> <!-- Username escolhido -->
                                    <p><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf_cliente']); ?></p> <!-- Documento do cliente -->
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email_cliente']); ?></p> <!-- E-mail cadastrado -->
                                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($cliente['telefone_cliente']); ?></p> <!-- Telefone para contato -->
                                    <p><strong>Idade:</strong> <?php echo $cliente['idade_cliente']; ?> anos</p> <!-- Idade informada -->
                                    <?php if ($cliente['data_cadastro']): ?>
                                        <p><strong>Cadastro:</strong> <?php echo date('d/m/Y H:i', strtotime($cliente['data_cadastro'])); ?></p> <!-- Data/hora de criação do registro -->
                                    <?php endif; ?>
                                </div>
                                <div class="usuario-badge">
                                    #<?php echo $index; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                
                <div class="usuarios-actions">
                    <a href="../pages/index.php" class="btn-voltar">← Página Principal</a>
                    <a href="../pages/index.php?page=cadastro" class="btn-cadastro-link">+ Novo Cliente</a>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>

</body>
</html>
<?php
if ($conn && $conn instanceof mysqli) {
    $conn->close(); // Fecha a conexão com o banco liberando recursos.
}
?>
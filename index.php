<?php
session_start();

$page = $_GET['page'] ?? 'home';

// Conectar ao banco de dados
$conn = include 'config.php';

$mensagem = "";
$acesso_negado = false;

// Verificar se está tentando acessar página de usuários
if ($page === 'usuarios') {
    $usuario_logado = $_SESSION['usuario_logado'] ?? null;
    
    if (!$usuario_logado) {
        $mensagem = "<div id='mensagem' class='mensagem error'>Você precisa fazer login para acessar esta página!</div>";
        $acesso_negado = true;
    } elseif (!($_SESSION['is_admin'] ?? false)) {
        $mensagem = "<div id='mensagem' class='mensagem error'>Acesso negado! Apenas administradores podem ver a lista de usuários.</div>";
        $acesso_negado = true;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($page === 'cadastro') {
        $nome_cliente = $conn->real_escape_string($_POST['nome_cliente'] ?? '');
        $cpf_cliente = $conn->real_escape_string($_POST['cpf_cliente'] ?? '');
        $idade_cliente = $conn->real_escape_string($_POST['idade_cliente'] ?? '');
        $telefone_cliente = $conn->real_escape_string($_POST['telefone_cliente'] ?? '');
        $email_cliente = $conn->real_escape_string($_POST['email_cliente'] ?? '');
        $username = $conn->real_escape_string($_POST['username'] ?? '');
        $password = $conn->real_escape_string($_POST['password'] ?? '');
        
        // echo "<pre>DEBUG: "; var_dump($_POST); echo "</pre>"; // Linha adicionada para debug

        if (!empty($nome_cliente) && !empty($cpf_cliente) && !empty($idade_cliente) && 
            !empty($telefone_cliente) && !empty($email_cliente) && !empty($username) && !empty($password)) {
            
            // Verificar se o username ou CPF já existe
            $check_query = "SELECT id_cliente FROM cliente WHERE username = '$username' OR cpf_cliente = '$cpf_cliente'";
            $check_result = $conn->query($check_query);
            
            if ($check_result->num_rows > 0) {
                $mensagem = "<div id='mensagem' class='mensagem error'>Usuário ou CPF já cadastrado!</div>";
            } else {
                // Inserir novo cliente
                $insert_query = "INSERT INTO cliente (nome_cliente, cpf_cliente, idade_cliente, telefone_cliente, email_cliente, username, password) 
                                VALUES ('$nome_cliente', '$cpf_cliente', '$idade_cliente', '$telefone_cliente', '$email_cliente', '$username', '$password')";
                
                // echo "<pre>DEBUG: "; var_dump($insert_query); echo "</pre>"; // Linha adicionada para debug

                if ($conn->query($insert_query)) {
                    $mensagem = "<div id='mensagem' class='mensagem success'>Cliente cadastrado com sucesso!</div>";
                } else {
                    $mensagem = "<div id='mensagem' class='mensagem error'>Erro ao cadastrar: " . $conn->error . "</div>";
                }
            }
        } else {
            $mensagem = "<div id='mensagem' class='mensagem error'>Preencha todos os campos!</div>";
        }
        
    } elseif ($page === 'login') {
        $username = $conn->real_escape_string($_POST['username'] ?? '');
        $password = $conn->real_escape_string($_POST['password'] ?? '');
        
        if (!empty($username) && !empty($password)) {
            // Buscar cliente no banco
            $login_query = "SELECT * FROM cliente WHERE username = '$username'";
            $result = $conn->query($login_query);
            
            if ($result->num_rows > 0) {
                $cliente = $result->fetch_assoc();
                // echo "<pre>DEBUG fetch_assoc: "; var_dump($cliente); echo "</pre>"; // adicionado para debug (linha 74)
                
                // Verificar senha
                if ($cliente['password'] === $password) {
                    $_SESSION['usuario_logado'] = $cliente['username'];
                    $_SESSION['nome_cliente'] = $cliente['nome_cliente'];
                    $_SESSION['is_admin'] = (bool)$cliente['is_admin'];
                    $_SESSION['id_cliente'] = $cliente['id_cliente'];
                    header('Location: home.php');
                    exit;
                } else {
                    $mensagem = "<div id='mensagem' class='mensagem error'>Senha incorreta!</div>";
                }
            } else {
                $mensagem = "<div id='mensagem' class='mensagem error'>Usuário não encontrado!</div>";
            }
        } else {
            $mensagem = "<div id='mensagem' class='mensagem error'>Preencha todos os campos!</div>";
        }
        
    } elseif ($page === 'logout') {
        session_destroy();
        header('Location: index.html');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($page); ?> - Sistema Clube da Fita</title>
    <link rel="stylesheet" href="index.css">
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

<body class="<?php echo $page; ?>">
    
    <?php echo $mensagem; ?>

    <?php if ($page === 'home'): ?>
        <div class="home-container">
            
            <h1>Clube da Fita</h1>
            
            <?php if (isset($_SESSION['usuario_logado'])): ?>
                <div class="usuario-info-box">
                    <p>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['nome_cliente'] ?? $_SESSION['usuario_logado']); ?></strong>!</p>
                    <?php if ($_SESSION['is_admin']): ?>
                        <span class="badge-admin">ADMINISTRADOR</span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>Bem-vindo! Escolha uma opção:</p>
            <?php endif; ?>
            
            <div class="buttons-container">
                <?php if (isset($_SESSION['usuario_logado'])): ?>
                    <a href="home.php" class="btn-link btn-destaque">
                        <span class="btn-icon">🎬</span>
                        Ir para Página Inicial
                    </a>
                <?php endif; ?>
                
                <?php if (!isset($_SESSION['usuario_logado'])): ?>
                    <a href="index.php?page=cadastro" class="btn-link">Fazer Cadastro</a>
                    <a href="index.php?page=login" class="btn-link">Fazer Login</a>
                <?php else: ?>
                    <?php if ($_SESSION['is_admin']): ?>
                        <a href="index.php?page=usuarios" class="btn-link">Ver Clientes</a>
                    <?php endif; ?>
                    <form method="POST" action="index.php?page=logout" style="width: 100%;">
                        <button type="submit" class="btn-link" style="width: 100%; border: none; cursor: pointer;">Sair</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="info-box">
                <p>Nós somos o clube da fita.</p>
                <p>Aqui você acha o filme que precisa e muito mais.</p>
            </div>
        </div>

    <?php elseif ($page === 'cadastro'): ?>
        <form id="form-cadastro" class="form-container form-cadastro-completo" action="index.php?page=cadastro" method="POST">
  
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
                        Já tem conta? <a href="index.php?page=login">Fazer Login</a>
                    </p>
                    
                    <a href="index.php" class="btn-home">← Área de Login</a>
                    <a href="index.html" class="btn-home-site">← Voltar ao Site</a>
                </div>
            </fieldset>
        </form>
        
    <?php elseif ($page === 'login'): ?>
        <form id="form-login" class="form-container" action="index.php?page=login" method="POST">
            
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
                    
                    <a href="index.php" class="btn-home">← Área de Login</a>
                    <a href="index.html" class="btn-home-site">← Voltar ao Site</a>
                </div>
            </fieldset>
        </form>
        
    <?php elseif ($page === 'usuarios'): ?>
        <?php if ($acesso_negado): ?>
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
                        <a href="index.php?page=login" class="btn-link">Fazer Login</a>
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
                $clientes_query = "SELECT * FROM cliente ORDER BY data_cadastro DESC";
                $clientes_result = $conn->query($clientes_query);
                
                if ($clientes_result->num_rows == 0): ?>
                    <div class="usuarios-vazio">
                        <p>Nenhum cliente cadastrado ainda.</p>
                        <a href="index.php?page=cadastro" class="btn-link">Fazer primeiro cadastro</a>
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
                        while ($cliente = $clientes_result->fetch_assoc()): 
                            $index++;
                        ?>
                            <div class="usuario-card cliente-card">
                                <div class="usuario-avatar">
                                    <?php echo strtoupper(substr($cliente['nome_cliente'], 0, 2)); ?>
                                </div>
                                <div class="usuario-info">
                                    <h3>
                                        <?php echo htmlspecialchars($cliente['nome_cliente']); ?>
                                        <?php if ($cliente['is_admin']): ?>
                                            <span class="badge-mini-admin">ADMIN</span>
                                        <?php endif; ?>
                                    </h3>
                                    <p><strong>Usuário:</strong> <?php echo htmlspecialchars($cliente['username']); ?></p>
                                    <p><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf_cliente']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email_cliente']); ?></p>
                                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($cliente['telefone_cliente']); ?></p>
                                    <p><strong>Idade:</strong> <?php echo $cliente['idade_cliente']; ?> anos</p>
                                    <?php if ($cliente['data_cadastro']): ?>
                                        <p><strong>Cadastro:</strong> <?php echo date('d/m/Y H:i', strtotime($cliente['data_cadastro'])); ?></p>
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
                    <a href="index.php" class="btn-voltar">← Página Principal</a>
                    <a href="index.php?page=cadastro" class="btn-cadastro-link">+ Novo Cliente</a>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>

</body>
</html>
<?php
$conn->close();
?>
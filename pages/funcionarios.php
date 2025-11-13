<?php
// Página simples (iniciante) de CRUD de Funcionários
// Requisitos: estar logado como admin

session_start();
if (!isset($_SESSION['usuario_logado']) || !($_SESSION['is_admin'] ?? false)) {
    header('Location: ../pages/index.php');
    exit;
}

$conn = require_once __DIR__ . '/../config/config.php'; // returns mysqli connection in $conn

$mensagem = '';

// Trata remoção via GET 
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($id > 0) {
        $sql = "DELETE FROM funcionario WHERE id_funcionario = $id";
        if ($conn->query($sql)) {
            $mensagem = 'Funcionário removido com sucesso!';
        } else {
            $mensagem = 'Erro ao remover: ' . $conn->error;
        }
    }
}

// Trata criação/edição via POST 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = $_POST['op'] ?? '';

    // Campos básicos
    $nome      = $conn->real_escape_string(trim($_POST['nome_funcionario'] ?? ''));
    $idade     = (int) ($_POST['idade_funcionario'] ?? 0);
    $salario   = $conn->real_escape_string(trim($_POST['salario_funcionario'] ?? '0'));
    $turno     = $conn->real_escape_string(trim($_POST['turno_funcionario'] ?? ''));
    $cargo     = $conn->real_escape_string(trim($_POST['cargo_funcionario'] ?? ''));
    $sexo      = $conn->real_escape_string(trim($_POST['sexo_funcionario'] ?? ''));
    $cpf       = $conn->real_escape_string(trim($_POST['cpf_funcionario'] ?? ''));
    $email     = $conn->real_escape_string(trim($_POST['email_funcionario'] ?? ''));

    if ($op === 'create') {
        if ($nome && $idade && $turno && $cargo && $cpf && $email) {
            $insert = "INSERT INTO funcionario (idade_funcionario, nome_funcionario, salario_funcionario, turno_funcionario, cargo_funcionario, sexo_funcionario, cpf_funcionario, email_funcionario)
                       VALUES ($idade, '$nome', '$salario', '$turno', '$cargo', '$sexo', '$cpf', '$email')";
            if ($conn->query($insert)) {
                header('Location: ../pages/funcionarios.php?msg=criado');
                exit;
            } else {
                $mensagem = 'Erro ao cadastrar: ' . $conn->error;
            }
        } else {
            $mensagem = 'Preencha os campos obrigatórios.';
        }
    } elseif ($op === 'edit') {
        $id = (int) ($_POST['id_funcionario'] ?? 0);
        if ($id > 0 && $nome && $idade && $turno && $cargo && $cpf && $email) {
            $update = "UPDATE funcionario SET
                        idade_funcionario = $idade,
                        nome_funcionario = '$nome',
                        salario_funcionario = '$salario',
                        turno_funcionario = '$turno',
                        cargo_funcionario = '$cargo',
                        sexo_funcionario = '$sexo',
                        cpf_funcionario = '$cpf',
                        email_funcionario = '$email'
                      WHERE id_funcionario = $id";
            if ($conn->query($update)) {
                header('Location: ../pages/funcionarios.php?msg=editado');
                exit;
            } else {
                $mensagem = 'Erro ao editar: ' . $conn->error;
            }
        } else {
            $mensagem = 'Preencha os campos obrigatórios.';
        }
    }
}

// Busca registro para edição quando solicitado
$editItem = null;
if (($_GET['action'] ?? '') === 'edit' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($id > 0) {
        $res = $conn->query("SELECT * FROM funcionario WHERE id_funcionario = $id");
        if ($res && $res->num_rows > 0) {
            $editItem = $res->fetch_assoc();
        }
    }
}

// Lista de funcionários
$lista = $conn->query("SELECT * FROM funcionario ORDER BY id_funcionario DESC");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Funcionários</title>
    <link rel="stylesheet" href="../css/funcionarios.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script>
        function confirmarDel(nome) {
            return confirm('Remover funcionário "' + nome + '"?');
        }
    </script>
    </head>
<body class="clientes">
    <div class="box">
        <h1>Admin - Funcionários</h1>
        <p class="badge">Você está logado como <strong><?php echo htmlspecialchars($_SESSION['usuario_logado']); ?></strong> (ADMIN)</p>

        <?php if (!empty($_GET['msg'])): ?>
            <div class="msg">Ação concluída: <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        <?php if (!empty($mensagem)): ?>
            <div class="msg"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <!-- Formulário de criação/edição simples -->
        <h2><?php echo $editItem ? 'Editar Funcionário' : 'Novo Funcionário'; ?></h2>
        <form class="simple" method="POST" action="funcionarios.php">
            <?php if ($editItem): ?>
                <input type="hidden" name="op" value="edit">
                <input type="hidden" name="id_funcionario" value="<?php echo (int)$editItem['id_funcionario']; ?>">
            <?php else: ?>
                <input type="hidden" name="op" value="create">
            <?php endif; ?>

            <input type="text" name="nome_funcionario" placeholder="Nome" required value="<?php echo htmlspecialchars($editItem['nome_funcionario'] ?? ''); ?>">
            <input type="number" name="idade_funcionario" placeholder="Idade" required value="<?php echo htmlspecialchars($editItem['idade_funcionario'] ?? ''); ?>">
            <input type="text" name="salario_funcionario" placeholder="Salário (ex.: 2500.00)" value="<?php echo htmlspecialchars($editItem['salario_funcionario'] ?? ''); ?>">
            <input type="text" name="turno_funcionario" placeholder="Turno (Manhã/Tarde/Noite)" required value="<?php echo htmlspecialchars($editItem['turno_funcionario'] ?? ''); ?>">
            <input type="text" name="cargo_funcionario" placeholder="Cargo" required value="<?php echo htmlspecialchars($editItem['cargo_funcionario'] ?? ''); ?>">
            <select name="sexo_funcionario">
                <option value="" <?php echo empty($editItem['sexo_funcionario']) ? 'selected' : ''; ?>>Sexo (opcional)</option>
                <option value="Masculino" <?php echo (($editItem['sexo_funcionario'] ?? '')==='Masculino')?'selected':''; ?>>Masculino</option>
                <option value="Feminino" <?php echo (($editItem['sexo_funcionario'] ?? '')==='Feminino')?'selected':''; ?>>Feminino</option>
                <option value="Outro" <?php echo (($editItem['sexo_funcionario'] ?? '')==='Outro')?'selected':''; ?>>Outro</option>
            </select>
            <input type="text" name="cpf_funcionario" placeholder="CPF" required value="<?php echo htmlspecialchars($editItem['cpf_funcionario'] ?? ''); ?>">
            <input type="email" name="email_funcionario" placeholder="E-mail" required value="<?php echo htmlspecialchars($editItem['email_funcionario'] ?? ''); ?>">
            <button type="submit"><i class="bi bi-save"></i> Salvar</button>
        </form>

        <!-- Lista simples de funcionários -->
        <div class="top-actions">
            <span class="badge">Total: <?php echo (int)($lista ? $lista->num_rows : 0); ?></span>
            <a href="../pages/home.php">← Voltar</a>
        </div>

    <div class="table-wrapper">
    <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Idade</th>
                    <th>Cargo</th>
                    <th>Turno</th>
                    <th>Salário</th>
                    <th>CPF</th>
                    <th>E-mail</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($lista && $lista->num_rows > 0): ?>
                    <?php while ($f = $lista->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$f['id_funcionario']; ?></td>
                            <td><?php echo htmlspecialchars($f['nome_funcionario']); ?></td>
                            <td><?php echo htmlspecialchars($f['idade_funcionario']); ?></td>
                            <td><?php echo htmlspecialchars($f['cargo_funcionario']); ?></td>
                            <td><?php echo htmlspecialchars($f['turno_funcionario']); ?></td>
                            <td><?php echo htmlspecialchars($f['salario_funcionario']); ?></td>
                            <td><?php echo htmlspecialchars($f['cpf_funcionario']); ?></td>
                            <td><?php echo htmlspecialchars($f['email_funcionario']); ?></td>
                            <td class="actions">
                                <a href="../pages/funcionarios.php?action=edit&id=<?php echo (int)$f['id_funcionario']; ?>">Editar</a>
                                <a href="../pages/funcionarios.php?action=delete&id=<?php echo (int)$f['id_funcionario']; ?>" onclick="return confirmarDel(<?php echo json_encode($f['nome_funcionario']); ?>)">Remover</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9">Nenhum funcionário cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
    </table>
    
<?php $conn->close(); ?>
</body>
</html>

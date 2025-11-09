<?php
// Arquivo: alugar.php — cria um registro simples de locação para o cliente logado
session_start();
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: index.php?page=login');
    exit;
}

$conn = include 'config.php';
$idCliente = (int)($_SESSION['id_cliente'] ?? 0);
$idFilme = (int)($_GET['id'] ?? 0);

if ($idCliente <= 0 || $idFilme <= 0) {
    header('Location: locadora.php?err=param');
    exit;
}

// Busca dados do filme para preencher nome_filme e coerência
$filmeSql = "SELECT id_filme, ident_titulo, estado_filme FROM filme WHERE id_filme = $idFilme";
$filmeRes = $conn->query($filmeSql);
if (!$filmeRes || $filmeRes->num_rows === 0) {
    header('Location: locadora.php?err=filme');
    exit;
}
$filme = $filmeRes->fetch_assoc();
$nomeFilme = $conn->real_escape_string($filme['ident_titulo']);
$estadoEnum = in_array((string)$filme['estado_filme'], ['1','2','3','4','5']) ? (string)$filme['estado_filme'] : '3';

// Garante que exista um funcionário para FK (pega o primeiro ou cria um de sistema)
$funcSql = "SELECT id_funcionario FROM funcionario ORDER BY id_funcionario ASC LIMIT 1";
$funcRes = $conn->query($funcSql);
if ($funcRes && $funcRes->num_rows > 0) {
    $idFuncionario = (int)$funcRes->fetch_assoc()['id_funcionario'];
} else {
    // Cria um funcionário padrão mínimo
    $cpfFake = '00000000001';
    $checkFunc = $conn->query("SELECT id_funcionario FROM funcionario WHERE cpf_funcionario = '$cpfFake' LIMIT 1");
    if ($checkFunc && $checkFunc->num_rows > 0) {
        $idFuncionario = (int)$checkFunc->fetch_assoc()['id_funcionario'];
    } else {
        $insFunc = "INSERT INTO funcionario (idade_funcionario, nome_funcionario, salario_funcionario, turno_funcionario, cargo_funcionario, sexo_funcionario, cpf_funcionario, email_funcionario) VALUES (30, 'Sistema', 0.00, 'Manha', 'Atendente', 'Outro', '$cpfFake', 'sistema@clubedafita.com')";
        if ($conn->query($insFunc)) {
            $idFuncionario = (int)$conn->insert_id;
        } else {
            header('Location: locadora.php?err=func');
            exit;
        }
    }
}

// Preços e campos padrão simplificados para viabilizar a gravação
$preco = '9.90';
$zero = '0.00';
$numeroSerie = rand(100000, 999999);

$insert = "INSERT INTO locacao (
    id_cliente, id_funcionario, id_filme, localizacao_filme, historico_aluguel, data_cadastro_filme, quantidade_copias,
    estado_filme, qtd_filmes_locados, numero_serie, nome_filme, preco_aluguel, alug_acres, acres_lancamentos,
    acres_estado_filme, alug_preco_fixo, alug_desc, desc_pontos_cliente, desc_feriado, desc_cartao_fidelidade
) VALUES (
    $idCliente, $idFuncionario, $idFilme, 'LOJA', 'Alugado', CURDATE(), 1,
    '$estadoEnum', 1, $numeroSerie, '$nomeFilme', $preco, $zero, $zero,
    $zero, $zero, $zero, $zero, $zero, $zero
)";

if ($conn->query($insert)) {
    header('Location: cliente_perfil.php?ok=1');
} else {
    header('Location: locadora.php?err=sql');
}

$conn->close();
?>

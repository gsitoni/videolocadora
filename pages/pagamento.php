<?php
// pagamento.php - versão simplificada e comentada em português
session_start();

// proteger rota
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: ../pages/index.php?page=login');
    exit;
}

// incluir conexão (config.php deve retornar o objeto mysqli ou definir $conn)
$conn = include __DIR__ . '/../config/config.php';
if (!($conn instanceof mysqli)) {
    require_once __DIR__ . '/../config/config.php';
    if (!isset($conn) || !($conn instanceof mysqli)) {
        die('Erro: conexão com o banco de dados indisponível.');
    }
}

// dados do usuário
$id_usuario = (int)($_SESSION['id_cliente'] ?? 0);
$usuario = $_SESSION['usuario_logado'] ?? '';

// id do filme (GET para visualizar, POST para processar)
$id_filme = (int)($_GET['id_filme'] ?? $_POST['id_filme'] ?? 0);

// variáveis para a view
$mensagem = '';
$filme = null;
$preco = 0.00;

/* ---------------------------------------------------------------------
   Funções simples e diretas
   ------------------------------------------------------------------ */

// busca filme por id
function buscar_filme($conn, $id) {
    $id = (int)$id;
    $sql = "SELECT id_filme, ident_titulo, ident_sinopse, imagem FROM filme WHERE id_filme = $id LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $res->num_rows) return $res->fetch_assoc();
    return null;
}

// tenta obter preco direto da tabela filme; se não, tenta preco_filme; senão heurística
function obter_preco($conn, $id, $titulo) {
    $id = (int)$id;
    // 1) tenta coluna preco_aluguel na própria tabela filme
    $res = $conn->query("SELECT preco_aluguel FROM filme WHERE id_filme = $id LIMIT 1");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        if (!empty($row['preco_aluguel'])) return floatval($row['preco_aluguel']);
    }

    // 2) tenta tabela preco_filme
    $res2 = $conn->query("SELECT preco FROM preco_filme WHERE id_filme = $id LIMIT 1");
    if ($res2 && $res2->num_rows) {
        $row2 = $res2->fetch_assoc();
        if (!empty($row2['preco'])) return floatval($row2['preco']);
    }

    // 3) heurística simples por título
    $t = strtolower($titulo ?? '');
    if (strpos($t, 'lanc') !== false || strpos($t, 'novo') !== false) return 9.90;
    if (strpos($t, 'cláss') !== false || strpos($t, 'class') !== false) return 3.90;
    return 5.90;
}

// tenta obter promoção (retorna array: ['percent' => 0.0, 'fixo' => null])
function obter_promocao($conn, $codigo) {
    $codigo = trim($codigo);
    if ($codigo === '') return ['porcentagem' => 0.0, 'fixo' => null];

    // tenta tabela promocoes (se existir)
    $pq = $conn->real_escape_string($codigo);
    $res = $conn->query("SELECT tipo, valor FROM promocoes WHERE codigo = '$pq' AND ativo = 1 LIMIT 1");
    if ($res && $res->num_rows) {
        $r = $res->fetch_assoc();
        if ($r['tipo'] === 'percent') return ['porcentagem' => floatval($r['valor']) / 100.0, 'fixo' => null];
        return ['porcentagem' => 0.0, 'fixo' => floatval($r['valor'])];
    }

    // fallback simples
    $c = strtoupper($codigo);
    if ($c === 'WEB10') return ['porcentagem' => 0.10, 'fixo' => null];
    if ($c === 'VIP20') return ['porcentagem' => 0.20, 'fixo' => null];
    if ($c === 'CIBER5') return ['porcentagem' => 0.0, 'fixo' => 5.00];
    return ['porcentagem' => 0.0, 'fixo' => null];
}

// verifica cartão fidelidade: se tabela existir exige registro ativo; se não existir, usa heurística VIP
// retorna array ['valido' => bool, 'percent' => float]
function verificar_cartao_fidelidade($conn, $cartao) {
    $cartao = trim($cartao);
    if ($cartao === '') return ['valido' => true, 'porcentagem' => 0.0];

    // tenta buscar na tabela (se existir)
    $c = $conn->real_escape_string($cartao);
    $res = $conn->query("SELECT desconto_percentagem FROM cartao_fidelidade WHERE numero_cartao = '$c' AND ativo = 1 LIMIT 1");
    if ($res) {
        if ($res->num_rows) {
            $r = $res->fetch_assoc();
            return ['valido' => true, 'porcentagem' => floatval($r['desconto_percentagem']) / 100.0];
        } else {
            // tabela existe mas não encontrou registro
            // para saber se a tabela existe, testamos se a consulta falhou por inexistência:
            // se a consulta devolveu objeto porém zero rows => tabela existe -> inválido
            return ['valido' => false, 'porcentagem' => 0.0];
        }
    }

    // se consulta falhou (possivelmente tabela não existe), aplica heurística
    if (stripos($cartao, 'VIP') === 0) return ['valido' => true, 'porcentagem' => 0.15];
    return ['valido' => true, 'porcentagem' => 0.0]; // aceita qualquer outro cartão quando não há tabela
}

// calcula juros por atraso: taxa fixa por dia (1.5% por dia)
function calcular_juros($preco, $dias, $taxa_diaria = 0.015) {
    $dias = (int)$dias;
    if ($dias <= 0) return 0.00;
    return round($preco * $dias * $taxa_diaria, 2);
}

/* ---------------------------------------------------------------------
   Fluxo principal: carregar filme e processar POST
   ------------------------------------------------------------------ */

if ($id_filme > 0) {
    $filme = buscar_filme($conn, $id_filme);
    if ($filme) {
        $preco = obter_preco($conn, $id_filme, $filme['ident_titulo']);
    }
}

// Processa pagamento quando POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_filme > 0 && $filme) {
    // dados do formulário (aceitamos qualquer dado de cartão)
    $nome_cartao = $_POST['cartao_name'] ?? '';
    $numero_cartao = $_POST['cartao_number'] ?? '';
    $validade = $_POST['cartao_validade'] ?? '';
    $cvv = $_POST['cartao_cvv'] ?? '';

    // preco enviado no hidden (se houver) ou valor já calculado
    $preco_postado = $_POST['preco_aluguel'] ?? null;
    if ($preco_postado !== null && $preco_postado !== '') {
        $preco = floatval(str_replace(',', '.', $preco_postado));
    }

    // dias de atraso (opcional) -> juros aplicados apenas se enviado
    $dias_atraso = (int)($_POST['dias_atraso'] ?? 0);
    $juros = calcular_juros($preco, $dias_atraso);

    // promoção
    $promocao = obter_promocao($conn, $_POST['promo_code'] ?? '');
    $promocao_porcentagem = $promocao['porcentagem'];
    $promocao_fixo = $promocao['fixo'];

    // fidelidade
    $cartao = trim($_POST['cartao_fidelidade'] ?? '');
    $fidelidade = verificar_cartao_fidelidade($conn, $cartao);
    if ($cartao !== '' && !$fidelidade['valido']) {
        $mensagem = 'Cartão fidelidade inválido ou inativo.';
    } else {
        $porcentagem_fidelidade = $fidelidade['porcentagem'];
        // cálculos simples
        $base = $preco;
        $valor_fidelidade = round($base * $porcentagem_fidelidade, 2);
        $apos_fidelidade = max(0.0, $base - $valor_fidelidade);

        if ($promocao_fixo !== null) {
            $valor_promo = min($promocao_fixo, $apos_fidelidade);
        } else {
            $valor_promo = round($apos_fidelidade * $promocao_porcentagem, 2);
        }

        $valor_final = round(max(0.0, $apos_fidelidade - $valor_promo) + $juros, 2);

        // grava locacao (campos mínimos com escape simples)
        $nome_filme = $conn->real_escape_string($filme['ident_titulo']);
        $localizacao = 'loja';
        $historico = 'novo aluguel';
        $data_cadastro = date('Y-m-d');
        $quantidade_copias = 1;
        $estado_filme = 1;
        $qtd_filmes_locados = 1;
        $numero_serie = rand(100000, 999999);

        $preco_sql = number_format($preco, 2, '.', '');
        $alug_preco_fixo_sql = number_format($valor_final, 2, '.', '');
        $alug_desc_sql = number_format($valor_promo, 2, '.', '');
        $desc_cart_sql = number_format($valor_fidelidade, 2, '.', '');

        $sqlLoc = "INSERT INTO locacao (id_cliente, id_funcionario, id_filme, localizacao_filme, historico_aluguel, data_cadastro_filme, quantidade_copias, estado_filme, qtd_filmes_locados, numero_serie, nome_filme, preco_aluguel, alug_acres, acres_lancamentos, acres_estado_filme, alug_preco_fixo, alug_desc, desc_pontos_cliente, desc_feriado, desc_cartao_fidelidade)
                   VALUES ({$id_usuario}, 1, {$id_filme}, '{$localizacao}', '{$historico}', '{$data_cadastro}', {$quantidade_copias}, {$estado_filme}, {$qtd_filmes_locados}, {$numero_serie}, '{$nome_filme}', {$preco_sql}, 0.00, 0.00, 0.00, {$alug_preco_fixo_sql}, {$alug_desc_sql}, 0.00, 0.00, {$desc_cart_sql})";

        if ($conn->query($sqlLoc)) {
            $id_locacao = $conn->insert_id;

            // grava pagamento (juros por atraso são gravados em alug_juros_atrasado)
            $quando = date('Y-m-d');
            $devolucao = date('Y-m-d', strtotime('+7 days'));
            $valor_final_sql = number_format($valor_final, 2, '.', '');
            $juros_sql = number_format($juros, 2, '.', '');
            $quem = $conn->real_escape_string($usuario);

            $sqlPag = "INSERT INTO pagamento (id_filme, id_locacao, lucro_pag, alug_pag, alug_juros_atrasado, alug_quando_alugou, alug_quem_alugou, alug_data_devolucao, alug_alteracao_monetaria)
                       VALUES ({$id_filme}, {$id_locacao}, 0.00, {$valor_final_sql}, {$juros_sql}, '{$quando}', '{$quem}', '{$devolucao}', 0.00)";
            if ($conn->query($sqlPag)) {
                $mensagem = 'Pagamento processado e locação registrada com sucesso! ID Locação: ' . $id_locacao;
                $detalhes = [];
                $detalhes[] = 'Base: R$ ' . number_format($base,2,',','.');
                if ($juros > 0) $detalhes[] = 'Juros (atraso): R$ ' . number_format($juros,2,',','.');
                if ($valor_fidelidade > 0) $detalhes[] = 'Desconto (fidelidade): -R$ ' . number_format($valor_fidelidade,2,',','.');
                if ($valor_promo > 0) $detalhes[] = 'Desconto (promoção): -R$ ' . number_format($valor_promo,2,',','.');
                $detalhes[] = 'Total cobrado: R$ ' . number_format($valor_final,2,',','.');
                $mensagem .= '<br>' . implode('<br>', array_map('htmlspecialchars', $detalhes));
            } else {
                $mensagem = 'Erro ao registrar pagamento: ' . htmlspecialchars($conn->error);
            }
        } else {
            $mensagem = 'Erro ao registrar locação: ' . htmlspecialchars($conn->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pagamento - <?php echo htmlspecialchars($filme['ident_titulo'] ?? 'Filme'); ?></title>
    <link rel="stylesheet" href="../css/pagamento.css" type="text/css">
</head>
<body>
    <div class="cartao">
        <h1>Pagamento</h1>
        <?php if ($mensagem): ?>
            <div style="padding:8px;background:#e6ffe6;border:1px solid #b6ffb6;margin-bottom:12px"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <?php if (!$filme): ?>
            <p>Filme não encontrado. Volte e escolha um filme.</p>
            <p><a href="../pages/home.php">Voltar</a></p>
        <?php else: ?>
            <div class="grid">
                <div>
                    <?php
                    $imgPath = '../img/poster-1.jpg';
                    if (!empty($filme['imagem'])) {
                        $img = $filme['imagem'];
                        if (strpos($img, 'images/') === 0) $imgPath = '../' . $img;
                        elseif (strpos($img, '/') === 0) $imgPath = $img;
                        else $imgPath = $img;
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="Capa">
                </div>
                <div style="flex:1">
                    <h2><?php echo htmlspecialchars($filme['ident_titulo']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($filme['ident_sinopse'])); ?></p>
                    <p><strong>Preço base:</strong> R$ <?php echo number_format((float)$preco,2,',','.'); ?></p>

                    <form method="post" action="../pages/pagamento.php">
                        <input type="hidden" name="id_filme" value="<?php echo $id_filme; ?>">
                        <input type="hidden" name="preco_aluguel" value="<?php echo htmlspecialchars(number_format($preco, 2, '.', '')); ?>">

                        <!-- REMOVIDO: Entrega Express / Extras -->

                        <!-- Promo / fidelidade -->
                        <div class="form-row"><label>Código promocional</label><input type="text" name="codigo_promocional" placeholder="Ex: SAVE10"></div>
                        <div class="form-row"><label>Cartão fidelidade (opcional)</label><input type="text" name="cartao_fidelidade" placeholder="Número do cartão"></div>

                        <!-- Para testar juros por atraso, envie dias_atraso no POST (campo hidden ou pelo cliente) -->
                        <!-- <input type="hidden" name="dias_atraso" value="3"> -->

                        <div class="form-row"><label>Nome no cartão</label><input type="text" name="cartao_nome"></div>
                        <div class="form-row"><label>Número do cartão</label><input type="text" name="cartao_numero"></div>
                        <div class="form-row"><label>Validade (MM/AA)</label><input type="text" name="cartao_validade"></div>
                        <div class="form-row"><label>CVV</label><input type="text" name="cartao_cvv"></div>

                        <div style="margin-top:10px">
                            <button type="submit">Pagar e Confirmar Aluguel</button>
                            <a href="../pages/catalogo.php" style="margin-left:12px">Voltar ao Catálogo</a>
                            <a href="../pages/home.php" style="margin-left:12px">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

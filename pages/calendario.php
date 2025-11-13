<?php
session_start();
$conn = include('../config/config.php');

// --- 1. Verifica se o usuário está logado ---
if (!isset($_SESSION['id_cliente'])) {
    die("Acesso negado. Faça login para ver seu calendário de devoluções.");
}

$id_cliente = $_SESSION['id_cliente'];

// --- 2. Define mês e ano atuais (ou recebe via GET) ---
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');

// --- 3. Calcula o primeiro dia do mês e o número total de dias ---
$primeiro_dia_mes = mktime(0, 0, 0, $mes, 1, $ano);
$dia_semana_inicio = date('w', $primeiro_dia_mes);
$dias_no_mes = date('t', $primeiro_dia_mes);

// --- 4. Consulta as devoluções desse mês ---
$sql = "SELECT 
            p.alug_data_devolucao,
            f.ident_titulo,
            c.nome_cliente
        FROM pagamento p
        INNER JOIN locacao l ON p.id_locacao = l.id_locacao
        INNER JOIN cliente c ON l.id_cliente = c.id_cliente
        INNER JOIN filme f ON p.id_filme = f.id_filme
        WHERE c.id_cliente = ?
          AND MONTH(p.alug_data_devolucao) = ?
          AND YEAR(p.alug_data_devolucao) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id_cliente, $mes, $ano);
$stmt->execute();
$result = $stmt->get_result();

// --- 5. Armazena devoluções por data ---
$devolucoes = [];
while ($row = $result->fetch_assoc()) {
    $data = $row['alug_data_devolucao'];
    $devolucoes[$data][] = $row['ident_titulo'];
}

$stmt->close();
$conn->close();

// --- 6. Array com nomes dos meses ---
$nomes_meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Calendário de Devoluções</title>
    <link rel="stylesheet" href="../css/calendario.css">
</head>
<body>

<div class="box">

    <!-- Título e navegação centralizados -->
    <h1>📅 Calendário de Devoluções - <?php echo $nomes_meses[$mes] . " de " . $ano; ?></h1>

    <div class="nav">
        <a href="?mes=<?php echo $mes == 1 ? 12 : $mes - 1; ?>&ano=<?php echo $mes == 1 ? $ano - 1 : $ano; ?>">◀ Mês anterior</a>
        <a href="?mes=<?php echo $mes == 12 ? 1 : $mes + 1; ?>&ano=<?php echo $mes == 12 ? $ano + 1 : $ano; ?>">Próximo mês ▶</a>
    </div>

    <table class="calendario">
        <tr>
            <th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th>
        </tr>
        <tr>
        <?php
        $dia = 1;

        // Preenche células vazias antes do início do mês
        for ($i = 0; $i < $dia_semana_inicio; $i++) {
            echo "<td class='vazio'></td>";
        }

        // Gera os dias do mês
        while ($dia <= $dias_no_mes) {
            $data_atual = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
            echo "<td>";
            echo "<div class='data'>{$dia}</div>";

            if (isset($devolucoes[$data_atual])) {
                foreach ($devolucoes[$data_atual] as $filme) {
                    echo "<div class='evento'>🎬 {$filme}</div>";
                }
            }

            echo "</td>";

            // Quebra de linha no sábado
            if ((($dia + $dia_semana_inicio) % 7) == 0) {
                echo "</tr><tr>";
            }

            $dia++;
        }

        // Preenche células vazias no fim
        $resto = (7 - (($dias_no_mes + $dia_semana_inicio) % 7)) % 7;
        for ($i = 0; $i < $resto; $i++) {
            echo "<td class='vazio'></td>";
        }
        ?>
        </tr>
    </table>

</div>

</body>
</html>
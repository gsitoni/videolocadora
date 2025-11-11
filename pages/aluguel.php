<?php
session_start();
require_once '../config/config.php'; // conexão com o banco

// pegar mensagem temporária (flash)
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
} else {
    $mensagem = "";
}

// se o usuário clicou em "Alugar"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alugar') {

    if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['mensagem'] = "<div class='mensagem error'>Você precisa fazer login para alugar um filme.</div>";
    header("Location: ../pages/login.php");
    exit;
    }

    $id_cliente = (int) $_SESSION['id_cliente'];
    $id_filme = (int) $_POST['id_filme'];

    // Buscar o preço do filme
    $stmt = $conn->prepare("SELECT preco_aluguel, nome_filme FROM filme WHERE id_filme = ?");
    $stmt->bind_param("i", $id_filme);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['mensagem'] = "<div class='mensagem error'>Filme não encontrado!</div>";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $filme = $result->fetch_assoc();
    $preco = $filme['preco_aluguel'];
    $nome  = $filme['nome_filme'];

    // Atualizar a tabela: vincular cliente e registrar histórico
    $historico = "Alugado por cliente #$id_cliente em " . date('d/m/Y H:i');

    $update = $conn->prepare("UPDATE filme SET id_cliente = ?, historico_aluguel = ? WHERE id_filme = ?");
    if (!$update) {
        $_SESSION['mensagem'] = "<div class='mensagem error'>Erro na preparação da query: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $update->bind_param("isi", $id_cliente, $historico, $id_filme);

    if ($update->execute()) {
        $_SESSION['mensagem'] = "<div class='mensagem success'>🎬 Filme <b>$nome</b> alugado com sucesso por R$ $preco!</div>";
    } else {
        $_SESSION['mensagem'] = "<div class='mensagem error'>Erro ao registrar aluguel: " . htmlspecialchars($update->error) . "</div>";
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alugar [Nome do Filme]</title>
    <link rel="stylesheet" href="../css/aluguel.css" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="header">
        <h1>CLUBE DA FITA</h1>
    </header>

    <main class="container">
        <section class="detalhes-filme">
            <div class="capa-filme">
                <img src="../img/poster-1-aluguel.jpg" alt="Capa do Filme">
            </div>

            <div class="info-locacao">
                <h2>Superman</h2>
                <p class="genero">Gênero: ação, Fantasia, ficção cientifica, aventura</p>
                <p class="duracao">Duração: 129 minutos</p>
                <p class="sinopse">
                    Sinopse: Um herói movido pela crença e pela esperança na bondade da humanidade. Em Superman, acompanhamos a jornada do super-herói em tentar conciliar suas duas personas: sua herança extraterrestre como kryptoniano e sua vida humana, criado como Clark Kent (David Corenswet) na cidade de Smallville no Kansas. Dirigido por James Gunn, o novo filme irá reunir personagens, heróis e vilões clássicos da história de Superman, como Lex Luthor (Nicholas Hoult), Lois Lane (Rachel Brosnahan), Lanterna Verde (Nathan Fillion), Mulher-Gavião (Isabela Merced), entre outros. O chamado de Superman será colocado à prova através de uma série de novas aventuras épicas e diante de uma sociedade que enxerga seus valores de justiça e verdade como antiquados.
                </p>

                <div class="preco-aluguel">
                    <span class="preco-label">Preço da Locação:</span>
                    <span class="preco">R$ 9,90</span>
                </div>

                <button class="btn-alugar" onclick="alert('Filme Alugado! Redirecionando para o Checkout...')">
                    Alugar Agora
                </button>

                <a href="../pages/index.html" class="btn-voltar">⬅️ Voltar para a Home</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Locadora | Todos os direitos reservados.</p>
    </footer>

    <link href="../js/aluguel.js">
</body>
</html>
// Script resiliente para múltiplas páginas: só ativa recursos quando os elementos existem

document.addEventListener('DOMContentLoaded', function () {
    // Interação da barra de pesquisa (só se existir no DOM)
    const buscar = document.querySelector('.barra-de-busca');
    const AbrirLupa = document.querySelector('.lupa-busca');
    const BotaoFechar = document.querySelector('.botao-de-fechar'); // botão opcional

    if (AbrirLupa && buscar) {
        AbrirLupa.addEventListener('click', () => buscar.classList.add('ativar'));
    }
    if (BotaoFechar && buscar) {
        BotaoFechar.addEventListener('click', () => buscar.classList.remove('ativar'));
    }

    // Cabeçalho com classe de rolagem (só se existir #header)
    const header = document.querySelector('#header');
    if (header) {
        window.addEventListener('scroll', function () {
            header.classList.toggle('rolagem', window.scrollY > 500);
        });
    }
});

// Carrossel dos filmes em cartaz - função segura e exposta no escopo global
(function attachCarousel() {
    // expõe rolar no window para ser usado por onclick inline sem ReferenceError
    window.rolar = function (distancia) {
        const carrossel = document.getElementById('carrossel');
        if (carrossel && typeof carrossel.scrollBy === 'function') {
            carrossel.scrollBy({ left: distancia, behavior: 'smooth' });
        } else {
            // evita erro no console quando o elemento não existe nesta página
            // opcional: console.debug('Carrossel não encontrado nesta página');
        }
    };
})();
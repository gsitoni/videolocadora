//Abre a interação da barra de pesquisa.
let buscar = document.querySelector('.barra-de-busca')
let AbrirLupa = document.querySelector('.lupa-busca')
let BotaoFechar = document.querySelector('.botao-de-fechar')

AbrirLupa.addEventListener('click', ()=> {
    buscar.classList.add('ativar')
})

BotaoFechar.addEventListener('click', ()=> {
    buscar.classList.remove('ativar')
})
//Fecha a interação da barra de pesquisa.

function pegaLocacoes() {
  fetch("api_locacao.php")
    .then(r => r.json())
    .then(dados => {
      const container = document.querySelector("#resultados");
      container.innerHTML = "";

      if (!dados.length) {
        container.innerHTML = "<p class='vazio'>Nenhum filme alugado encontrado.</p>";
        return;
      }

      let carrossel = document.createElement("div");
      carrossel.classList.add("carrossel");

      dados.forEach(filme => {
        const card = document.createElement("div");
        card.classList.add("filme-card");
        const imagemPath = filme.imagem ? `../${filme.imagem}` : '../img/poster_padrao.png';
        card.innerHTML = `
          <img src="${imagemPath}" alt="${filme.nome_filme}">
          <div class="info-filme">
            <h3>${filme.nome_filme}</h3>
            <p>Preço: R$ ${parseFloat(filme.preco_aluguel).toFixed(2).replace('.', ',')}</p>
            <p>Data: ${filme.data_cadastro_filme}</p>
            <p class="historico">${filme.historico_aluguel}</p>
          </div>
        `;
        carrossel.appendChild(card);
      });

      container.appendChild(carrossel);
    })
    .catch(() => {
      document.querySelector("#resultados").innerHTML = "Erro ao carregar filmes.";
    });
}

pegaLocacoes();
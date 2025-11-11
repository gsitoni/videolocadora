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

//Abre a interação do cabeçalho/scroll da tela.
window.addEventListener("scroll", function(){
    let header = document.querySelector('#header')
    header.classList.toggle('rolagem', window.scrollY > 500)
})

//Carrossel dos filmes em cartaz - Animação.
const carrossel = document.getElementById('carrossel'); 
function rolar(distancia) { 
    carrossel.scrollBy({ left: distancia, behavior: 'smooth' }); 
}
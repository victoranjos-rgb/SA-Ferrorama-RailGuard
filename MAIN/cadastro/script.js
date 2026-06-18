const botaoCadastrar = document.getElementById("botaoCadastrar");

function verificarCargo(){
    botaoCadastrar.addEventListener('click', () => {
        const cargo = document.getElementById("cargo");
        const valor = cargo.value;

        if(valor === "membro"){
            window.location.href = "paginaInicialMembro.html"
        }else if(valor ==="gestor"){
            window.location.href = "paginaInicialGestor.html"
        }else if(valor === "maquinista"){
            window.location.href = "paginaInicialMaquinista.html"
        }
    });
}

<<<<<<< HEAD
const buttonCadastro = document.getElementById("buttonCadastro");

function verificarCargo(){
    const cargo = document.getElementById("cargo");
    const verificarCargo = cargo.value;

    if(verificarCargo === "membro"){
        window.location.href = "../PaginaInicial/paginaInicialMembro.html";
    }else if(verificarCargo === "gestor"){
        window.location.href = "../PaginaInicial/paginaInicialGestor.html";
    }else{
        window.location.href = "../PaginaInicial/paginaInicialMaquinista.html"
    }
}
=======
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
>>>>>>> b894d9b03849915063c0d743e344f348fe456368

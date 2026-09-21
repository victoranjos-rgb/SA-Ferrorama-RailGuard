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

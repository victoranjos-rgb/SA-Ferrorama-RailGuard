const selecionarTerminal = document.getElementById("selecionarTerminal");

function verificarLocal(){
    const selectCidade = document.getElementById("cidade");
    const cidade = selectCidade.value;

    if(cidade === "terminalNorte"){
        window.location.href = "../terminalCargas/terminalNorte/terminalNorte.html";
    }else if(cidade === "terminalSul"){
        window.location.href = "../terminalCargas/terminalSul/terminalSul.html";
    }else if(cidade ==="teminalCentro"){
        window.location.href = "../terminalCargas/terminalCentro/terminalCentro.html";
    }else{
        alert("Selecione uma opção valida")
    }
}

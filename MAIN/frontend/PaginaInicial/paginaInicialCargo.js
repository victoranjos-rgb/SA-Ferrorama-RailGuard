// Proteção das páginas por cargo desenvolvida com auxílio de IA (OpenAI Codex).
const API = "../../backend/api/";
const cargoEsperado = document.body.dataset.cargo;
const paginas = {
  gestor: "paginaInicialGestor.html",
  membro: "paginaInicialMembro.html",
  maquinista: "paginaInicialMaquinista.html",
};
async function iniciar() {
  try {
    const resposta = await fetch(API + "sessao.php", {
        credentials: "same-origin",
      }),
      dados = await resposta.json();
    if (!resposta.ok || !dados.usuario) {
      location.href = "../login/login.html";
      return;
    }
    if (dados.usuario.cargo !== cargoEsperado) {
      location.href = paginas[dados.usuario.cargo] || "../login/login.html";
      return;
    }
    document.getElementById("nome-usuario").textContent = dados.usuario.nome;
  } catch {
    location.href = "../login/login.html";
  }
}
document.getElementById("sair").addEventListener("click", async () => {
  await fetch(API + "logout.php", {
    method: "POST",
    credentials: "same-origin",
  });
  location.href = "../login/login.html";
});
iniciar();

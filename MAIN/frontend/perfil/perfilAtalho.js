(async () => {
  const inicio = location.pathname.indexOf("/MAIN/frontend/");
  if (inicio < 0) return;
  const base = location.pathname.slice(0, inicio + 15);
  if (!window.RailGuardTema && !document.querySelector('script[data-tema-railguard]')) {
    const tema = document.createElement('script');
    tema.src = base + 'componentes/tema.js?v=20260929-1';
    tema.dataset.temaRailguard = 'true';
    document.head.appendChild(tema);
  }
  const link = document.createElement("a");
  link.href = base + "perfil/perfil.html";
  link.className = "atalho-perfil";
  link.setAttribute("aria-label", "Abrir meu perfil");
  link.title = "Meu perfil";
  const imagem = document.createElement("img");
  imagem.src = base + "Imagens/do-utilizador.png";
  imagem.alt = "";
  link.append(imagem);
  document.body.append(link);
  const estilo = document.createElement("style");
  estilo.textContent =
    ".atalho-perfil{position:fixed;right:clamp(15px,3vw,34px);top:clamp(15px,3vw,28px);z-index:50;width:48px;height:48px;display:grid;place-items:center;border:1px solid #8b98a8;border-radius:50%;background:#3a4653;color:#fff;text-decoration:none;box-shadow:0 5px 12px #0006;transition:transform .18s,background .18s}.atalho-perfil img{width:27px;height:27px;object-fit:contain;filter:brightness(0) invert(1)}.atalho-perfil:hover{transform:translateY(-2px);background:#557ca8}";
  document.head.append(estilo);
  try {
    const raizProjeto = location.pathname.slice(0, inicio);
    const resposta = await fetch(raizProjeto + '/MAIN/backend/api/sessao.php', {credentials: 'same-origin'});
    const dados = await resposta.json();
    const paginas = {gestor: 'paginaInicialGestor.html', membro: 'paginaInicialMembro.html', maquinista: 'paginaInicialMaquinista.html'};
    const pagina = paginas[dados.usuario?.cargo];
    if (pagina) {
      document.querySelectorAll('.voltar,.botao-voltar,.voltar-icone').forEach(item => {
        if (!item.href.includes('dashboard.html')) item.href = base + 'PaginaInicial/' + pagina;
      });
    }
  } catch {}
})();

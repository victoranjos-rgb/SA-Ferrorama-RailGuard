// Tema global desenvolvido com auxílio de IA (OpenAI Codex).
(() => {
  if (window.RailGuardTema) return;
  const base = new URL('./', document.currentScript.src);
  const possuiEstiloDaTela = document.querySelectorAll('link[rel="stylesheet"]:not([data-tema-railguard])').length > 0;
  document.body.classList.toggle('pagina-legada', !possuiEstiloDaTela);
  if (!document.querySelector('link[data-tema-railguard]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = new URL('tema.css?v=20260929-1', base).href;
    link.dataset.temaRailguard = 'true';
    document.head.appendChild(link);
  }
  const aplicar = () => {
    const claro = localStorage.getItem('railguard_tema') === 'claro';
    document.body.classList.toggle('tema-claro', claro);
    document.body.classList.toggle('claro', claro);
    const botao = document.querySelector('.botao-tema-global');
    if (botao) {
      botao.textContent = claro ? '☾' : '☀';
      botao.title = claro ? 'Ativar modo escuro' : 'Ativar modo claro';
      botao.setAttribute('aria-label', botao.title);
      botao.setAttribute('aria-pressed', String(claro));
    }
  };
  document.body.classList.toggle('modo-consulta', new URLSearchParams(location.search).get('modo') === 'consulta');
  const alternar = () => {
    localStorage.setItem('railguard_tema', document.body.classList.contains('tema-claro') ? 'escuro' : 'claro');
    aplicar();
  };
  const botao = document.createElement('button');
  botao.type = 'button';
  botao.className = 'botao-tema-global';
  botao.addEventListener('click', alternar);
  document.body.appendChild(botao);
  window.RailGuardTema = {aplicar, alternar};
  aplicar();
})();

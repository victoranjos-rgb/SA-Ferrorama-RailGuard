// Componente desenvolvido com auxílio de IA (OpenAI Codex).
(() => {
  const iconeVisivel = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/></svg>';
  const iconeOculto = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 6.1A10.8 10.8 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-2.1 2.8M6.6 6.6C3.6 8.4 2 12 2 12s3.5 6 10 6a10.8 10.8 0 0 0 4.1-.8M9.9 9.9a3 3 0 0 0 4.2 4.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

  document.querySelectorAll('input[type="password"]').forEach(campo => {
    const contenedor = campo.parentElement;
    if (!contenedor || contenedor.querySelector('.alternar-senha')) return;
    contenedor.classList.add('campo-senha');

    const botao = document.createElement('button');
    botao.type = 'button';
    botao.className = 'alternar-senha';
    botao.setAttribute('aria-label', 'Mostrar senha');
    botao.setAttribute('aria-pressed', 'false');
    botao.title = 'Mostrar senha';
    botao.innerHTML = iconeVisivel;

    botao.addEventListener('click', () => {
      const mostrar = campo.type === 'password';
      campo.type = mostrar ? 'text' : 'password';
      botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
      botao.setAttribute('aria-pressed', String(mostrar));
      botao.title = mostrar ? 'Ocultar senha' : 'Mostrar senha';
      botao.innerHTML = mostrar ? iconeOculto : iconeVisivel;
    });

    contenedor.appendChild(botao);
  });
})();

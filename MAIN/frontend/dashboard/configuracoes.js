// Preferências locais implementadas com auxílio de IA (OpenAI Codex).
const botaoTema=document.querySelector('#tema');
const botaoNotificacoes=document.querySelector('#notificacoes');
function aplicar(){const claro=localStorage.getItem('railguard_tema')==='claro';const notificar=localStorage.getItem('railguard_notificacoes')!=='desativadas';document.body.classList.toggle('claro',claro);document.body.classList.toggle('tema-claro',claro);botaoTema.textContent=claro?'Ativado':'Desativado';botaoTema.setAttribute('aria-pressed',String(claro));botaoNotificacoes.textContent=notificar?'Ativadas':'Desativadas';botaoNotificacoes.setAttribute('aria-pressed',String(notificar));window.RailGuardTema?.aplicar()}
botaoTema.addEventListener('click',()=>{localStorage.setItem('railguard_tema',document.body.classList.contains('tema-claro')||document.body.classList.contains('claro')?'escuro':'claro');aplicar()});
botaoNotificacoes.addEventListener('click',()=>{localStorage.setItem('railguard_notificacoes',botaoNotificacoes.getAttribute('aria-pressed')==='true'?'desativadas':'ativadas');aplicar()});
document.querySelector('#logout').addEventListener('click',async()=>{await fetch('../../backend/api/logout.php',{method:'POST',credentials:'same-origin'});location.href='../login/login.html'});aplicar();

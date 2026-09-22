const API = '../../backend/api/';
const area = document.querySelector('#perfil');

function texto(valor) {
    return valor ? String(valor) : 'Não informado';
}

async function carregarPerfil() {
    try {
        const resposta = await fetch(API + 'perfil.php', { credentials: 'same-origin' });
        const dados = await resposta.json();
        if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível carregar o perfil.');

        const perfil = dados.perfil;
        const campos = [
            ['Nome', perfil.nome],
            ['Nome de usuário', perfil.nome_usuario],
            ['E-mail', perfil.email],
            ['Cargo', perfil.cargo],
            ['Status de acesso', perfil.status_acesso],
            ['País', perfil.pais],
            ['Estado', perfil.estado],
            ['Cidade', perfil.cidade],
            ['Conta criada em', perfil.criado_em]
        ];

        area.innerHTML = campos.map(([rotulo, valor]) =>
            '<div class="item"><span>' + rotulo + '</span><strong>' + texto(valor) + '</strong></div>'
        ).join('');
    } catch (erro) {
        area.textContent = erro.message;
    }
}
carregarPerfil();


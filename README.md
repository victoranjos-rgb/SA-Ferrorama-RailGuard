# RailGuard — SA Ferrorama

Sistema web para apoiar a operação de um ferrorama, desenvolvido com HTML, CSS, JavaScript, PHP e MySQL.

## Como executar

1. Coloque o projeto em `C:\xampp\htdocs\SA-Ferrorama-RailGuard\SA-Ferrorama-RailGuard`.
2. Inicie Apache e MySQL no XAMPP.
3. Configure `MAIN/backend/config.php` a partir de `config.exemplo.php`.
4. Importe `MAIN/backend/bancoUsuario.sql` no banco MySQL configurado.
5. Execute as migrações necessárias no terminal PHP, como `migrar_rotas.php`, `migrar_manutencoes_trilhos.php` e `migrar_cargas.php`.
6. Acesse pelo navegador: `http://localhost/SA-Ferrorama-RailGuard/SA-Ferrorama-RailGuard/MAIN/frontend/login/login.html`.

## Recursos implementados

- Cadastro, login, sessão e aprovação de usuários por gestor.
- Gestão de trens, rotas, manutenções de trens e manutenções de trilhos.
- Cadastro de cargas, monitoramento por código/prefixo e relatórios das cargas.
- Perfil do usuário disponível nas telas autenticadas.
- Recuperação de senha com código temporário.
- Dashboard e dados de demonstração.
- Layout responsivo para celular, tablet e computador.

## Estrutura principal

- `MAIN/frontend/`: telas HTML, CSS e JavaScript.
- `MAIN/backend/api/`: APIs JSON usadas pelas telas.
- `MAIN/backend/`: conexão, migrações e scripts de manutenção.
- `tests/`: testes do backend.
- `REGISTRO_IA.md`: registro das mudanças feitas com auxílio de IA.

## Observações importantes

- O servidor está configurado para responder páginas em UTF-8 por meio de `.htaccess`.
- Para enviar códigos de recuperação por e-mail de verdade, configure a opção de e-mail em `MAIN/backend/config.php` e o envio de e-mail do XAMPP/SMTP. Sem essa configuração, o fluxo serve apenas para teste local.
- Não versione `config.php`, certificados ou senhas.

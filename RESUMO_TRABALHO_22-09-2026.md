# Resumo do trabalho — 22/09/2026

## Objetivo do dia

Evoluir o sistema RailGuard para permitir o acompanhamento de cargas, disponibilizar perfil de usuário e recuperação de senha, padronizar telas e corrigir problemas de codificação visual.

## Funcionalidades implementadas

### Cargas e relatórios

- Criada a estrutura de dados de cargas e a migração correspondente.
- Criada a API para cadastrar e consultar cargas.
- Criada a tela **Cadastrar carga**.
- Ao informar o código de uma rota, como `LIN-243`, a tela de cadastro preenche automaticamente informações disponíveis da rota: origem, destino, trem, status e previsão.
- Criada a tela **Monitorar cargas**, com busca por código, prefixo do trem, nome da carga, origem e destino.
- O monitoramento também exibe rotas já cadastradas, para permitir acompanhar códigos como `LIN-243` antes de existir uma carga associada.
- Criada a API e integração da tela **Relatórios e análises**, mostrando últimas cargas e resumo por status.
- Corrigido o tratamento de horário exibido nos relatórios para o fuso de São Paulo.

Arquivos principais:

- `MAIN/backend/api/cargas.php`
- `MAIN/backend/api/relatorios.php`
- `MAIN/backend/garantir_cargas.php`
- `MAIN/backend/migrar_cargas.php`
- `MAIN/frontend/cargas/`
- `MAIN/frontend/RelatorioAnalise/relatorioAnalise.js`

### Recuperação de senha

- Implementado fluxo de solicitação de código, verificação e redefinição de senha.
- Criadas APIs para cada etapa e migração da tabela de códigos temporários.
- As telas de recuperação passaram a enviar dados ao backend em vez de apenas mudar de página.

Arquivos principais:

- `MAIN/backend/api/solicitar_recuperacao_senha.php`
- `MAIN/backend/api/verificar_codigo_recuperacao.php`
- `MAIN/backend/api/redefinir_senha.php`
- `MAIN/backend/migrar_recuperacao_senha.php`
- `MAIN/frontend/EsqueceuSenha/`

> Importante: o envio de e-mail real depende de configurar SMTP/e-mail no `config.php` e no XAMPP. Sem essa configuração, o fluxo é apropriado para testes locais.

### Perfil do usuário

- Criada a página **Meu perfil**, que mostra dados da conta autenticada.
- Criada a API que entrega os dados do usuário da sessão.
- Adicionado atalho de perfil nas telas autenticadas.
- Incluído o ícone `Imagens/do-utilizador.png`.
- Corrigido o layout do cartão do perfil, com rótulos e valores organizados em colunas e quebra para textos longos.

Arquivos principais:

- `MAIN/backend/api/perfil.php`
- `MAIN/frontend/perfil/perfil.html`
- `MAIN/frontend/perfil/perfil.css`
- `MAIN/frontend/perfil/perfil.js`
- `MAIN/frontend/perfil/perfilAtalho.js`

### Interface e navegação

- Adicionado atalho para **Cadastrar carga** na página inicial do gestor.
- Melhorada a aparência e responsividade do monitoramento de cargas.
- Ajustados os botões de voltar das telas de cargas.
- Ajustado o botão **← Voltar** da página de aprovação de acessos para não ficar atrás do ícone de perfil.
- Tratadas respostas inválidas das APIs para apresentar mensagens compreensíveis no lugar de erros como `Unexpected token '<'`.

### Codificação UTF-8

- Corrigidos textos que apareciam com acentos quebrados.
- Criado o arquivo `.htaccess` com `AddDefaultCharset UTF-8`.
- Revisados arquivos HTML, CSS, JS, PHP, SQL e documentação.
- Validada a ausência de sequências de codificação corrompida e de caracteres substitutos.
- Criada cópia de segurança antes da restauração: `.codex-backups/antes-correcao-codificacao.patch`.

## Documentação atualizada

- `README.md`: visão geral do projeto, estrutura, execução e observações.
- `REGISTRO_IA.md`: registro detalhado das alterações realizadas com IA.
- Este arquivo: resumo do trabalho realizado no dia.

## Pontos para testar no XAMPP

1. Cadastre uma carga usando uma rota existente, por exemplo `LIN-243`.
2. Confirme a carga em **Monitorar cargas** e em **Relatórios e análises**.
3. Teste a tela de perfil com uma conta autenticada.
4. Teste o fluxo de recuperação de senha.
5. Atualize o navegador com `Ctrl + F5` se aparecerem arquivos antigos no cache.
6. Execute as migrações de cargas e recuperação de senha caso as tabelas ainda não existam.

## Próximos cuidados

- Configurar SMTP antes de usar recuperação de senha com e-mails reais.
- Não versionar `config.php`, certificados ou senhas.
- Validar os fluxos completos no XAMPP antes da apresentação.

---

# Atualização do trabalho — 25/09/2026

## Objetivo do dia

Continuar a construção das telas operacionais do RailGuard, corrigir problemas visuais encontrados durante os testes e acrescentar o gerenciamento dos sensores instalados nos trilhos.

## Funcionalidades e melhorias implementadas

### Trens cadastrados

- Corrigido o posicionamento e a aparência do botão **Novo trem**.
- Adicionado e posteriormente padronizado o botão de voltar com uma seta simples.
- Mantida a listagem dos trens existentes, com ações para editar e excluir.

Arquivos principais:

- `MAIN/frontend/trens/trensCadastrados.html`
- `MAIN/frontend/trens/trens.css`

### Relatórios e análises

- Reconstruída a apresentação dos indicadores das cargas.
- Melhorada a aparência e a responsividade da tabela de movimentações.
- Adicionado tratamento para sessão expirada e erros retornados pela API.
- Mantida a integração com os dados reais cadastrados no sistema.

Arquivos principais:

- `MAIN/frontend/RelatorioAnalise/relatorioAnalise.html`
- `MAIN/frontend/RelatorioAnalise/relatorioAnalise.css`
- `MAIN/frontend/RelatorioAnalise/relatorioAnalise.js`

### Manutenção dos trilhos

- Reconstruída a tela de cadastro e histórico de manutenções dos trilhos.
- Implementado o vínculo da manutenção com uma rota existente.
- Criados os campos de localização, descrição, datas, prioridade, situação e responsável.
- Corrigido o carregamento das rotas no seletor.
- Criada no Aiven a tabela `manutencoes_trilhos`.
- Validada a regra RN10: uma rota com manutenção aberta ou em andamento fica bloqueada; ao concluir ou cancelar a manutenção, a rota é liberada.

Arquivos principais:

- `MAIN/backend/api/manutencoes_trilhos.php`
- `MAIN/backend/migrar_manutencoes_trilhos.php`
- `MAIN/frontend/Trilhos/manuntecaoTrilhos.html`
- `MAIN/frontend/Trilhos/trilhos.css`
- `MAIN/frontend/Trilhos/trilhos.js`
- `tests/test_manutencoes_trilhos.php`

### Padronização dos botões de voltar

- Os botões com texto e caixa foram substituídos pela seta simples utilizada no Dashboard.
- A alteração foi aplicada nas telas de trens, aprovações, cargas, relatórios, perfil e manutenção dos trilhos.
- Foram incluídos `aria-label` e `title` para manter a acessibilidade.
- O posicionamento foi ajustado para celular, tablet e computador.

### Correção da tela Finalizar Cadastro

- Corrigido o baixo contraste do seletor de cargo.
- As opções agora possuem fundo escuro e texto claro.
- A opção selecionada possui destaque azul com texto branco.
- O CSS recebeu versionamento na URL para evitar que o navegador reutilize uma versão antiga em cache.

Arquivos principais:

- `MAIN/frontend/cadastro/finalizarCadastro.html`
- `MAIN/frontend/cadastro/cadastro.css`

### CRUD de sensores dos trilhos

- Consultado o repositório `ProfCercal/crud-trens`, que possui estrutura para leituras de sensores e um simulador de dados.
- Criado o cadastro dos dispositivos que serão instalados nos trilhos.
- Adicionados os tipos de sensor:
  - proximidade;
  - velocidade;
  - vibração;
  - temperatura.
- Cada sensor pode possuir código, modelo, rota, localização, unidade de medida, limite para alerta e situação operacional.
- Criada uma tela com:
  - cadastro e edição;
  - listagem e pesquisa;
  - exclusão;
  - indicadores de sensores ativos, em manutenção e com alerta;
  - layout responsivo para celular, tablet e computador.
- A alteração dos sensores foi restrita aos gestores.
- Adicionado o acesso **Sensores dos trilhos** na página inicial do gestor.
- Criada a tabela `sensores` no banco Aiven.

Arquivos principais:

- `MAIN/backend/migrar_sensores.php`
- `MAIN/backend/api/sensores.php`
- `MAIN/frontend/sensores/sensores.html`
- `MAIN/frontend/sensores/sensores.css`
- `MAIN/frontend/sensores/sensores.js`
- `tests/test_crud_sensores.php`

### Visualização de senhas

- Criado um componente reutilizável com botão de olho para mostrar ou ocultar a senha.
- O botão foi incluído nas telas de login, cadastro e redefinição de senha.
- O ícone muda para um olho cortado quando a senha está visível.
- Cada campo pode ser controlado separadamente.
- O botão utiliza `type="button"`, portanto não envia o formulário acidentalmente.
- Foram adicionadas descrições acessíveis para **Mostrar senha** e **Ocultar senha**.

Arquivos principais:

- `MAIN/frontend/componentes/senha.css`
- `MAIN/frontend/componentes/senha.js`
- `MAIN/frontend/login/login.html`
- `MAIN/frontend/cadastro/cadastro.html`
- `MAIN/frontend/EsqueceuSenha/novaSenha.html`

## Testes executados

- Verificação de sintaxe dos arquivos PHP relacionados aos sensores.
- Verificação de sintaxe dos novos arquivos JavaScript.
- Teste automatizado do CRUD de sensores:
  - criar sensor: aprovado;
  - listar sensor: aprovado;
  - editar sensor: aprovado;
  - excluir sensor: aprovado.
- Teste automatizado da manutenção dos trilhos e do bloqueio de rotas: aprovado.
- Verificação HTTP das páginas e dos arquivos CSS/JavaScript: respostas `200`.
- Verificação de formatação com `git diff --check` sem erros nas alterações.

## Próximas etapas sugeridas

- Criar a tabela e a API de leituras enviadas pelos sensores.
- Adaptar o simulador do professor para gerar dados enquanto os dispositivos físicos não estiverem disponíveis.
- Exibir alertas em tempo real quando uma leitura ultrapassar o limite configurado.
- Relacionar leituras de proximidade e velocidade com os trens que estiverem percorrendo cada rota.
- Validar visualmente todas as telas nos tamanhos de até 400 px, tablet de até 800 px e computador.


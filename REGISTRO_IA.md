# Registro de uso de inteligência artificial

Este documento registra as partes do projeto RailGuard desenvolvidas ou alteradas com auxílio de inteligência artificial.

## 22/09/2026 — Estrutura inicial do banco de usuários

- Ferramenta utilizada: OpenAI Codex.
- Arquivo alterado: `MAIN/backend/bancoUsuario.sql`.
- Trabalho realizado:
  - criação do banco `railguard` com codificação `utf8mb4`;
  - criação da tabela `usuarios`;
  - definição dos campos de identificação, acesso, localização e cargo;
  - criação de restrições de unicidade para nome de usuário e e-mail;
  - criação de índices para cargo e situação do usuário;
  - inclusão de datas automáticas de criação e atualização.
- Revisão humana necessária: confirmar os campos exigidos pelo professor e executar o script no MySQL do XAMPP antes de utilizar no Aiven.

## 22/09/2026 — Configuração da conexão PHP com MySQL

- Ferramenta utilizada: OpenAI Codex.
- Arquivos criados ou alterados:
  - `.gitignore`;
  - `MAIN/backend/config.exemplo.php`;
  - `MAIN/backend/conexao.php`.
- Trabalho realizado:
  - criação de um modelo de configuração para XAMPP e Aiven;
  - separação das credenciais do código versionado;
  - criação da conexão MySQL com `mysqli` e codificação `utf8mb4`;
  - suporte opcional ao certificado CA exigido pela conexão segura com o Aiven;
  - validação da existência e dos campos do arquivo de configuração.
- Revisão humana necessária: criar `config.php` a partir do exemplo, preencher as credenciais corretas e testar a conexão nos ambientes local e remoto.

## 22/09/2026 — API inicial de autenticação

- Ferramenta utilizada: OpenAI Codex.
- Arquivos criados:
  - `MAIN/backend/api/resposta.php`;
  - `MAIN/backend/api/sessao_helper.php`;
  - `MAIN/backend/api/cadastro.php`;
  - `MAIN/backend/api/login.php`;
  - `MAIN/backend/api/sessao.php`;
  - `MAIN/backend/api/logout.php`.
- Trabalho realizado:
  - criação de respostas JSON padronizadas;
  - leitura de dados enviados como JSON ou formulário;
  - validação dos dados de cadastro;
  - cadastro com consultas preparadas e `password_hash()`;
  - login com `password_verify()` e regeneração do identificador da sessão;
  - consulta do usuário autenticado e encerramento da sessão;
  - configuração dos cookies de sessão com `HttpOnly` e `SameSite=Lax`.
- Revisão humana necessária: preencher `config.php`, importar o banco e testar as rotas usando o Apache do XAMPP.

## 22/09/2026 — Importador do banco pelo terminal

- Ferramenta utilizada: OpenAI Codex.
- Arquivos criados ou alterados:
  - `.gitignore`;
  - `MAIN/backend/bancoUsuario.sql`;
  - `MAIN/backend/importar_banco.php`.
- Trabalho realizado:
  - criação de um importador que reutiliza com segurança o `config.php`;
  - bloqueio da execução do importador pelo navegador;
  - remoção dos comandos de criação e seleção do banco, pois o banco `railguard` já foi criado na Aiven;
  - proteção do certificado CA localizado na pasta `certificado`.
- Revisão humana necessária: executar o importador pelo terminal e confirmar a criação da tabela `usuarios`.

## 22/09/2026 — Integração do cadastro com a API

- Ferramenta utilizada: OpenAI Codex.
- Arquivos alterados:
  - `MAIN/frontend/cadastro/cadastro.html`;
  - `MAIN/frontend/cadastro/finalizarCadastro.html`;
  - `MAIN/frontend/cadastro/script.js`.
- Trabalho realizado:
  - transformação das duas etapas de cadastro em formulários HTML válidos;
  - inclusão de identificadores, nomes, limites e campos obrigatórios;
  - validação da confirmação de senha;
  - armazenamento temporário da primeira etapa em `sessionStorage`;
  - envio dos dados para `backend/api/cadastro.php` usando `fetch()` e JSON;
  - exibição de mensagens de sucesso e erro;
  - redirecionamento para o login após o cadastro.
- Revisão humana necessária: testar o fluxo completo pelo Apache e conferir a gravação do usuário no banco da Aiven.

## 22/09/2026 — Integração do login com a API

- Ferramenta utilizada: OpenAI Codex.
- Arquivos criados ou alterados:
  - `MAIN/frontend/login/login.html`;
  - `MAIN/frontend/login/script.js`;
  - `MAIN/frontend/login/sytle.css`.
- Trabalho realizado:
  - transformação da tela de login em um formulário válido;
  - envio de e-mail e senha para `backend/api/login.php` usando JSON;
  - exibição das mensagens retornadas pela API;
  - criação da sessão autenticada no backend;
  - redirecionamento para a página correspondente ao cargo do usuário.
- Revisão humana necessária: entrar com o usuário cadastrado e confirmar o redirecionamento conforme o cargo.

## 22/09/2026 — Etapa 1: aprovação de acesso pelo gestor

- Ferramenta utilizada: OpenAI Codex.
- Arquivos criados ou alterados:
  - `MAIN/backend/bancoUsuario.sql`;
  - `MAIN/backend/migrar_aprovacao.php`;
  - `MAIN/backend/gerenciar_gestor.php`;
  - `MAIN/backend/api/cadastro.php`;
  - `MAIN/backend/api/login.php`;
  - `MAIN/backend/api/sessao_helper.php`;
  - `MAIN/backend/api/usuarios_pendentes.php`;
  - `MAIN/backend/api/analisar_usuario.php`;
  - `MAIN/frontend/gestor/aprovacoes.html`;
  - `MAIN/frontend/gestor/aprovacoes.css`;
  - `MAIN/frontend/gestor/aprovacoes.js`;
  - `MAIN/frontend/PaginaInicial/paginaInicialGestor.html`;
  - `tests/test_aprovacao.php`.
- Trabalho realizado:
  - criação dos estados pendente, aprovado, rejeitado e bloqueado;
  - criação de utilitário local para definir o primeiro gestor;
  - preservação das contas existentes durante a migração;
  - bloqueio de login antes da aprovação;
  - autorização das rotas administrativas apenas para gestores;
  - painel para aprovação, rejeição e confirmação do cargo;
  - responsividade para celular, tablet e computador;
  - teste automatizado do fluxo completo com limpeza dos dados temporários.
- Revisão humana necessária: escolher quais usuários reais terão o cargo de gestor e testar visualmente o painel.

## 22/09/2026 — Etapa 2: CRUD de trens

- Ferramenta utilizada: OpenAI Codex.
- Base utilizada: repositório `ProfCercal/crud-trens`, adaptado ao domínio e à autenticação do RailGuard.
- Trabalho realizado:
  - tabela de trens com prefixo único, modelo, combustível, ano, capacidade, velocidade e situação;
  - API com criação, listagem, consulta, edição e exclusão;
  - escrita restrita a gestores autenticados;
  - telas responsivas para celular, tablet e computador;
  - teste automatizado de todas as operações com limpeza dos dados temporários.

## 22/09/2026 — Etapa 3: CRUD de manutenções de trens

- Ferramenta utilizada: OpenAI Codex.
- Referência visual: tela `Manunteção dos trilhos` (nó `9:460`) do Figma, adaptada ao RF09.
- Trabalho realizado:
  - tabela e API para criar, listar, editar e excluir manutenções;
  - relacionamento da manutenção com o trem e escrita restrita ao gestor;
  - aplicação da RN05, bloqueando automaticamente o trem durante uma manutenção em andamento;
  - tela responsiva para celular, tablet e computador no padrão visual do Figma;
  - teste automatizado do CRUD e da mudança de situação do trem.
- Revisão humana necessária: validar com o professor os campos escolhidos e conferir o visual.

## 22/09/2026 — Dados de demonstração dos trens

- Ferramenta utilizada: OpenAI Codex.
- Arquivo criado: `MAIN/backend/adicionar_trens_exemplo.php`.
- Trabalho realizado: adaptação dos cinco registros enviados pelo usuário, completando combustível e velocidade exigidos pela tabela e evitando duplicação pelo prefixo.

## 22/09/2026 — Refinamento da página inicial do gestor

- Ferramenta utilizada: OpenAI Codex com referência do Figma, nó `7:331`.
- Trabalho realizado: reconstrução responsiva, atalhos dos módulos, verificação do cargo e botão de saída.

## 22/09/2026 — Dashboard, termos e configurações

- Ferramenta utilizada: OpenAI Codex com os nós `7:392`, `9:516` e `11:1118` do Figma.
- Trabalho realizado: dashboard responsivo, clima de Joinville via Open-Meteo, gráfico com dados reais, resumo operacional, termos de uso, preferências locais e logout.
- Revisão humana necessária: revisar juridicamente o texto dos termos antes de publicar.

## 22/09/2026 — Correções do dashboard e cadastro

- Ferramenta utilizada: OpenAI Codex.
- Trabalho realizado: substituição do gráfico em canvas por barras HTML/CSS responsivas e redirecionamento para evitar o erro 404 em `PaginaInicial/cadastro.html`.

## 22/09/2026 — Gestão de rotas com mapa

- Ferramenta utilizada: OpenAI Codex e Figma, nó `9:297`.
- Trabalho realizado: tabela e CRUD de rotas, horários, status, avisos, vínculo opcional com trem e mapa Leaflet/OpenStreetMap com origem, destino e traçado.
- Observação: o mapa mostra coordenadas cadastradas; localização em tempo real dependerá dos sensores.

## 22/09/2026 — Simplificação e correção do mapa de rotas

- Ferramenta utilizada: OpenAI Codex.
- Trabalho realizado: remoção dos campos visíveis de coordenadas, seleção por estações, distância automática e correção da inicialização e do redimensionamento do Leaflet.

## 22/09/2026 — Manutenção dos trilhos

- Ferramenta utilizada: OpenAI Codex e Figma, nós `9:460` e `9:479`.
- Trabalho realizado: CRUD de manutenções dos trilhos, vínculo com rota, prioridade, responsável, histórico responsivo e bloqueio automático da rota conforme RN10.

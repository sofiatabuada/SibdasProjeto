========================================================================
 MediTrack
 Sistema de Gestão de Inventário Hospitalar de Equipamentos Médicos
========================================================================

  Unidade curricular : SIBDAS — Sistemas de Informação e Base de Dados
                       Aplicados à Saúde
  Curso              : LEBIOM — Licenciatura em Engenharia Biomédica
  Ano letivo         : 2025-2026
  Estudante          : Sofia Tabuada
  Número de estudante: 1221408

========================================================================
 1. DESCRIÇÃO DO PROJETO
========================================================================
O MediTrack é uma aplicação web que simula um sistema de gestão do
inventário hospitalar de equipamentos médicos.

Permite registar e gerir, de forma organizada, toda a informação
associada ao funcionamento de um inventário hospitalar: equipamentos,
fornecedores, localizações, documentação técnica, garantias,
manutenções, movimentações e empréstimos entre serviços. Disponibiliza
ainda um dashboard com indicadores e gráficos, uma área pública
institucional e uma área reservada protegida por autenticação.

A aplicação está dividida em duas áreas:
  - Área pública  : acessível sem autenticação (página institucional,
                    informações e formulário de contacto).
  - Área reservada: acessível após login; contém todo o sistema de
                    gestão do inventário.

========================================================================
 2. TECNOLOGIAS UTILIZADAS
========================================================================
  - PHP 8 (sem frameworks)
  - MySQL
  - HTML5, CSS3 e JavaScript
  - Bootstrap 5.3.3 (versão mais recente; interface responsiva)
  - Font Awesome (ícones)
  - jQuery
  - DataTables (tabelas com pesquisa, ordenação e paginação)
  - Chart.js (gráficos do dashboard)
  - Flatpickr (seleção de datas)

========================================================================
 3. ESTRUTURA DE DIRETÓRIOS
========================================================================
  meditrack/
  ├── index.php                 -> ponto de entrada (encaminha p/ a área pública)
  ├── README.txt                -> este ficheiro
  ├── meditrack.sql             -> cópia da base de dados (estrutura + dados)
  ├── commits.txt               -> registo dos commits do repositório Git
  │
  ├── config/
  │   └── config.php            -> configuração (ligação à BD e constantes)
  │
  ├── public/                   -> ÁREA PÚBLICA (sem autenticação)
  │   ├── index.php             -> página inicial institucional
  │   ├── login.php             -> página de autenticação
  │   ├── logout.php            -> terminar sessão
  │   └── processa_contacto.php -> processamento do formulário de contacto
  │
  ├── private/                  -> ÁREA RESERVADA (requer autenticação)
  │   ├── index.php             -> bloqueio do acesso direto à pasta
  │   ├── home.php              -> dashboard (indicadores e gráficos)
  │   ├── processa_login.php    -> validação do login
  │   │
  │   ├── includes/             -> elementos comuns às páginas
  │   │   ├── header.php        -> cabeçalho HTML e folhas de estilo
  │   │   ├── nav.php           -> barra de navegação superior
  │   │   ├── sidebar.php       -> menu lateral
  │   │   ├── footer.php        -> rodapé e scripts
  │   │   ├── breadcrumb.php    -> navegação estrutural (migalhas)
  │   │   ├── funcoes.php       -> funções auxiliares (ligação à BD, logs...)
  │   │   └── validacoes.php    -> funções de validação de dados
  │   │
  │   ├── ajax/
  │   │   └── notificacoes.php  -> notificações (pedidos assíncronos)
  │   │
  │   └── views/                -> MÓDULOS da aplicação
  │       ├── equipamentos/     -> gestão de equipamentos (CRUD + ficha de detalhe)
  │       ├── fornecedores/     -> gestão de fornecedores
  │       ├── localizacoes/     -> gestão de localizações
  │       ├── documentos/       -> documentos dos equipamentos
  │       ├── garantias/        -> garantias
  │       ├── componentes/      -> componentes dos equipamentos
  │       ├── manutencoes/      -> manutenções
  │       ├── movimentacoes/    -> movimentações entre localizações
  │       ├── emprestimos/      -> empréstimos entre serviços
  │       ├── etiquetas/        -> etiquetas com QR code
  │       ├── exportar/         -> exportações (CSV, JSON, PDF)
  │       ├── historico/        -> histórico de eventos (auditoria; só admin)
  │       └── backoffice/       -> gestão de conteúdos da área pública
  │
  └── assets/                   -> recursos estáticos
      ├── css/1221408.css       -> estilos próprios (identificados pelo nº de aluno)
      ├── js/1221408.js         -> scripts próprios
      ├── img/                  -> imagens
      ├── bootstrap/            -> Bootstrap 5.3.3 (CSS + JS)
      ├── jQuery/               -> jQuery
      ├── datatables/           -> DataTables
      ├── flatpickr/            -> Flatpickr (seleção de datas)
      └── fontawesome/          -> Font Awesome (ícones)

========================================================================
 4. INSTALAÇÃO E EXECUÇÃO
========================================================================
A base de dados está alojada no servidor da unidade curricular
(vsgate-s1.dei.isep.ipp.pt, porta 10464). A aplicação liga-se
diretamente a esse servidor, pelo que NÃO é necessário importar a base
de dados para executar o projeto.

  PASSOS:
  1. Colocar a pasta do projeto na raiz do servidor web, no caminho:
         sibdas/1221408/meditrack
  2. Confirmar que, no ficheiro config/config.php, está definido:
         define('DB_ENV', 'isep');     (valor já definido por defeito)
  3. Abrir no navegador o endereço:
         http://127.0.0.1/sibdas/1221408/meditrack

  O endereço acima abre a ÁREA PÚBLICA. Para entrar na área reservada,
  usar o botão "Entrar" (ou aceder a .../public/login.php) e introduzir
  as credenciais indicadas na secção 5.

  NOTA SOBRE O CAMINHO:
  A aplicação calcula automaticamente o seu caminho base, pelo que
  funciona em qualquer pasta de instalação, sem necessidade de
  alterações no código.

  EXECUÇÃO COM BASE DE DADOS LOCAL (opcional):
  Caso se pretenda executar com uma base de dados local (ex.: MAMP/
  XAMPP), em vez do servidor:
  1. Importar o ficheiro meditrack.sql para uma base de dados chamada
     "meditrack".
  2. No config/config.php, mudar para:
         define('DB_ENV', 'local');
     (utilizador "root" / password "root" por defeito; ajustar
     conforme o ambiente, na secção 'local' do config.php).

========================================================================
 5. CREDENCIAIS DE ACESSO
========================================================================
A aplicação tem dois perfis de utilizador: ADMINISTRADOR e AGENTE.

  -------------------------------------------------------------------
  PERFIL          | EMAIL                  | PASSWORD
  -------------------------------------------------------------------
  Administrador   | admin@meditrack.pt     | admin123
  Agente          | agente1@meditrack.pt   | agente123
  Agente          | agente2@meditrack.pt   | agente123
  -------------------------------------------------------------------

  DIFERENÇAS ENTRE PERFIS:
  - Administrador: acesso total, incluindo a secção de Administração
    (gestão da Área Pública e página de Histórico de eventos).
  - Agente: acesso à gestão do inventário, sem a secção de
    Administração.

  Na página de login existem ainda botões de "Acesso rápido para
  testes" que preenchem automaticamente as credenciais de cada perfil.

========================================================================
 6. PRINCIPAIS TESTES A REALIZAR
========================================================================
  AUTENTICAÇÃO E SEGURANÇA
   1. Entrar com o administrador (admin@meditrack.pt / admin123).
   2. Tentar entrar com uma password errada — o acesso deve ser
      recusado, com mensagem de erro.
   3. Terminar sessão (logout) e confirmar que a sessão é encerrada.
   4. Sem login, tentar aceder diretamente a uma página reservada
      (ex.: .../private/home.php) — deve redirecionar para o login.

  GESTÃO DO INVENTÁRIO (CRUD)
   5. Equipamentos: criar um novo equipamento, vê-lo na listagem,
      pesquisar/filtrar, editar, abrir a ficha de detalhe e, por fim,
      apagar (com página de confirmação antes de eliminar).
   6. Repetir operações semelhantes noutros módulos (fornecedores,
      localizações, manutenções, etc.).

  DASHBOARD
   7. Abrir o dashboard e verificar os indicadores e gráficos
      (totais por categoria, estado, etc.).

  EXPORTAÇÃO DE DADOS
   8. Na listagem de equipamentos, exportar em Excel (CSV), em PDF e
      em JSON, e abrir os ficheiros gerados.

  GESTÃO DA ÁREA PÚBLICA (apenas administrador)
   9. Na secção de Administração, alterar um conteúdo da área pública
      e confirmar que a alteração aparece na página inicial pública.

  HISTÓRICO / AUDITORIA (apenas administrador)
  10. Após as ações anteriores, abrir "Histórico" no menu lateral e
      confirmar o registo dos eventos (login, criação, edição,
      eliminação, etc.).

========================================================================
 7. FUNCIONALIDADES E ASPETOS TÉCNICOS
========================================================================
  - Operações CRUD completas (criar, listar, editar, eliminar) em
    todos os módulos, com validação no lado do servidor.
  - Eliminação com página de confirmação prévia (soft delete: os
    registos são marcados como eliminados, não removidos da BD).
  - Pesquisa, filtragem, ordenação e paginação nas listagens
    (DataTables).
  - Interface construída com componentes do Bootstrap (cards, badges,
    alertas, modais, dropdowns, navbar), incluindo o componente
    Accordion na página pública (componente não explorado nas aulas).
  - Dashboard com indicadores e gráficos (Chart.js).
  - Geração automática do código de inventário (formato MT-AAAA-NNN).
  - Geração de etiquetas com QR code para os equipamentos.
  - Exportação de dados em CSV, JSON e PDF.
  - Gestão de conteúdos da área pública através da área reservada
    (backoffice), evitando a edição direta do HTML.
  - Registo de eventos (auditoria) na tabela "logs", consultável na
    página "Histórico": tentativas de autenticação, criação/edição/
    eliminação de dados e erros.

  SEGURANÇA
  - Passwords guardadas de forma segura com password_hash() (bcrypt);
    a verificação é feita com password_verify().
  - Acesso às áreas restritas protegido por autenticação e sessões;
    nenhuma página reservada é acessível sem login.
  - Identificadores (IDs) e emails encriptados nos URLs com
    AES-256-CBC, evitando a exposição direta de chaves da base de dados.
  - Consultas SQL parametrizadas (prepared statements), prevenindo
    injeção de SQL.

========================================================================
 8. BASE DE DADOS
========================================================================
  - A base de dados encontra-se no servidor da unidade curricular
    (vsgate-s1.dei.isep.ipp.pt:10464), base "db1221408".
  - O ficheiro meditrack.sql, incluído na raiz do projeto, contém a
    estrutura completa e os dados, permitindo recriar integralmente a
    base de dados (importar para uma base de dados vazia).
  - Principais tabelas: agentes, equipamentos, fornecedores,
    localizacoes, equipamento_fornecedor, documentos, garantias,
    manutencoes, movimentacoes, emprestimos, componentes,
    conteudos_publicos, contactos e logs.

========================================================================
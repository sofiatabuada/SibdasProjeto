# MediTrack — Sistema Web de Apoio ao Inventário Hospitalar de Equipamentos Médicos

**Unidade Curricular:** Sistemas de Informação e Base de Dados Aplicados à Saúde (SIBDAS)  
**Curso:** LEBIOM — 2025/2026  
**Número de aluno:** 1221408  
**Instituto:** ISEP — Instituto Superior de Engenharia do Porto

---

## Descrição do Projeto

A **MediTrack** é uma aplicação web desenvolvida para simular o trabalho de uma empresa de software especializada em sistemas de informação para a área da saúde. O sistema permite gerir o inventário de equipamentos médicos de uma unidade hospitalar, centralizando informação que anteriormente se encontrava dispersa em folhas de Excel, registos manuais e pastas físicas.

O projeto é composto por duas componentes distintas:

- **Front Office** — Website institucional da empresa MediTrack, com informações sobre a empresa, funcionalidades do sistema e formulário de contacto.
- **Back Office** — Aplicação web funcional utilizada pelo hospital para gerir o inventário dos equipamentos médicos.

---

## Estrutura de Diretórios

```
MediTrack/
├── assets/                          ← Assets partilhados (público e privado)
│   ├── bootstrap/                   ← Bootstrap 5.3
│   ├── fontawesome/                 ← Font Awesome 6.5
│   ├── jQuery/                      ← jQuery 3.6.0
│   ├── datatables/                  ← DataTables 1.13.1
│   ├── flatpickr/                   ← Flatpickr (seletor de datas)
│   ├── css/
│   │   └── 1221408.css              ← Estilos personalizados
│   ├── js/
│   │   └── 1221408.js               ← Scripts personalizados
│   └── img/                         ← Imagens
├── config/
│   └── config.php                   ← Configurações da aplicação e BD
├── public/                          ← Área pública (sem autenticação)
│   ├── index.php                    ← Landing page institucional
│   ├── login.php                    ← Página de login
│   ├── logout.php                   ← Terminar sessão
│   └── processa_contacto.php        ← Processar formulário de contacto
├── private/                         ← Área reservada (requer autenticação)
│   ├── index.php                    ← Redireciona para dashboard
│   ├── home.php                     ← Dashboard principal
│   ├── processa_login.php           ← Verificação de credenciais
│   ├── uploads/                     ← Ficheiros uploaded pelos utilizadores
│   ├── includes/                    ← Componentes reutilizáveis
│   │   ├── header.php
│   │   ├── nav.php
│   │   ├── sidebar.php
│   │   ├── footer.php
│   │   └── funcoes.php              ← Funções auxiliares (BD, sessão, AES)
│   └── views/                       ← Vistas por módulo
│       ├── equipamentos/            ← CRUD + pesquisa + detalhes
│       ├── componentes/             ← Componentes associados a equipamentos
│       ├── fornecedores/            ← CRUD de fornecedores
│       ├── localizacoes/            ← CRUD de localizações
│       ├── documentos/              ← CRUD de documentação técnica
│       ├── garantias/               ← CRUD de garantias e contratos
│       └── backoffice/              ← Gestão de conteúdos e mensagens
├── meditrack.sql                    ← Estrutura e dados iniciais da BD
└── README.md                        ← Este ficheiro
```

---

## Tecnologias Utilizadas

| Componente | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5.3, JavaScript |
| Backend | PHP 8.3 |
| Base de dados | MySQL (MAMP) |
| Bibliotecas JS | jQuery 3.6.0, DataTables 1.13.1, Flatpickr, Chart.js |
| Ícones | Font Awesome 6.5 |
| Tipografia | DM Serif Display + DM Sans (Google Fonts) |

---

## Módulos Implementados

### 1. Módulo de Equipamentos
Núcleo principal do sistema. Permite inserir, listar, consultar, editar e remover equipamentos médicos. Cada equipamento possui código de inventário gerado automaticamente, designação, categoria, marca, modelo, número de série, fabricante, data de aquisição, ano de fabrico, custo, tipo de entrada, estado e criticidade clínica.

**Regras de negócio implementadas:**
- Código de inventário único e gerado automaticamente no formato MT-AAAA-NNN
- Número de série não pode ser duplicado para o mesmo fabricante e modelo
- Soft delete — equipamentos removidos ficam arquivados com deleted_at

### 2. Módulo de Componentes
Permite associar componentes e acessórios a equipamentos. O código do componente é gerado automaticamente herdando o código do equipamento pai (ex: MT-2022-001.01). Visível diretamente na ficha do equipamento.

### 3. Módulo de Localizações
Organiza os equipamentos por localização física: edifício, piso, serviço/departamento e sala.

### 4. Módulo de Fornecedores
Regista fabricantes, distribuidores, empresas de assistência técnica e fornecedores de consumíveis. Suporta associação de múltiplos fornecedores a cada equipamento (relação N:M).

### 5. Módulo de Documentação
Permite associar documentos técnicos a equipamentos: manuais, certificados de calibração, contratos de manutenção, faturas, declarações de conformidade e relatórios técnicos. Suporta upload real de ficheiros (PDF, Word, Excel, imagens até 10MB).

### 6. Módulo de Garantias e Contratos
Regista datas de início e fim de garantia, existência de contrato de manutenção, tipo de contrato, entidade responsável e periodicidade. Acessível diretamente na ficha do equipamento.

### 7. Módulo de Pesquisa e Filtragem
Permite pesquisar equipamentos por: designação, código, marca, modelo, número de série, categoria, estado, criticidade, localização e fornecedor. Suporta filtros combinados com tabela de resultados ordenável.

### 8. Dashboard
Página inicial com indicadores de síntese:
- Total de equipamentos, ativos, em manutenção e inativos
- Total de fornecedores, localizações e documentos
- Garantias expiradas e a expirar nos próximos 30 dias
- Equipamentos críticos (alta + suporte de vida)
- Equipamentos sem documentação associada
- Alertas automáticos com links diretos
- Gráficos por estado, criticidade, categoria e serviço (Chart.js)

---

## Front Office

O website público inclui:
- Landing page com secções Hero, Sobre, Funcionalidades, Módulos e Contactos
- Formulário de contacto funcional — mensagens guardadas na base de dados
- Conteúdos dinâmicos editáveis pelo administrador no backoffice

---

## Back Office — Área de Administração

Acessível apenas ao perfil Admin:
- **Área Pública** — editar textos e informações de contacto do site público
- **Mensagens** — consultar mensagens enviadas pelo formulário de contacto, marcar como lidas e responder por email

---

## Estrutura da Base de Dados

A base de dados meditrack é composta por 10 tabelas:

| Tabela | Descrição |
|---|---|
| agentes | Utilizadores do sistema (admin/agente). Email encriptado com AES |
| equipamentos | Inventário de equipamentos médicos |
| componentes | Componentes e acessórios associados a equipamentos |
| localizacoes | Localizações físicas dos equipamentos |
| fornecedores | Fabricantes, distribuidores e assistência técnica |
| equipamento_fornecedor | Relação N:M entre equipamentos e fornecedores |
| documentos | Documentação técnica associada a equipamentos |
| garantias | Garantias e contratos de manutenção |
| contactos | Mensagens recebidas pelo formulário público |
| conteudos_publicos | Textos editáveis do site público |

**Decisões de modelação:**
- Relação N:M entre equipamentos e fornecedores através de tabela intermédia
- Soft delete em equipamentos, fornecedores e agentes (campo deleted_at)
- Encriptação AES-256 para emails dos agentes na base de dados
- IDs nunca expostos em URLs — encriptados com OpenSSL AES-256-CBC
- Componentes em tabela separada com FK para equipamentos (CASCADE DELETE)

---

## Componentes Bootstrap não explorados nas aulas

- **Accordion** — utilizado na secção "Módulos" da landing page (public/index.php)
- **Alertas dinâmicos** — utilizados no dashboard para notificações de garantias e documentação

---

## Funcionalidades Opcionais Implementadas

- Upload real de ficheiros PDF e outros formatos
- Alertas visuais para garantias a expirar (30 dias)
- Classificação de criticidade com destaque visual (badges coloridos)
- Dashboard com gráficos (Chart.js)
- Área de administração para gestão de conteúdos públicos
- Geração automática de códigos de inventário
- Estrutura de componentes associados a equipamentos

---

## Como Instalar e Executar

1. Copiar a pasta MediTrack para Applications/MAMP/htdocs/
2. Iniciar o MAMP (Apache + MySQL, porta 8888)
3. Abrir http://localhost:8888/phpMyAdmin
4. Criar a base de dados meditrack com collation utf8mb4_unicode_ci
5. Importar o ficheiro meditrack.sql
6. Aceder a http://localhost:8888/MediTrack/public/

**Credenciais de teste:**

| Utilizador | Password | Perfil |
|---|---|---|
| admin@meditrack.pt | $2y$10$aia | Admin |
| agente1@meditrack.pt | $2y$10$O | Agente |
| agente2@meditrack.pt | $2y$10$B5 | Agente |

---

## Declaração de Utilização de IA

No desenvolvimento deste projeto foi utilizado o assistente de inteligência artificial **Claude (Anthropic)** como ferramenta de apoio ao desenvolvimento. A IA foi utilizada para:
- Geração e revisão de código PHP, HTML, CSS e JavaScript
- Apoio na estruturação da base de dados
- Depuração e resolução de erros
- Sugestões de boas práticas de desenvolvimento web

Todo o código foi revisto, testado e adaptado pelo autor. A utilização da IA foi uma ferramenta de produtividade, sendo o estudante responsável por todas as decisões de arquitetura, design e implementação do sistema.
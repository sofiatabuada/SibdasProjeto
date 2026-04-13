-- --------------------------------------------------------
-- MediTrack — Base de Dados
-- meditrack.sql
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `meditrack`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `meditrack`;

-- --------------------------------------------------------
-- Tabela: agentes (utilizadores do sistema)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agentes` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`       VARBINARY(200)   DEFAULT NULL,
    `passwrd`    VARCHAR(200)     DEFAULT NULL,
    `profile`    VARCHAR(20)      DEFAULT NULL,
    `last_login` DATETIME         DEFAULT NULL,
    `created_at` DATETIME         DEFAULT NOW(),
    `deleted_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: localizacoes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `localizacoes` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `edificio`      VARCHAR(100)    DEFAULT NULL,
    `piso`          VARCHAR(50)     DEFAULT NULL,
    `servico`       VARCHAR(100)    NOT NULL,
    `sala`          VARCHAR(100)    DEFAULT NULL,
    `observacoes`   TEXT            DEFAULT NULL,
    `created_at`    DATETIME        DEFAULT NOW(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: fornecedores
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fornecedores` (
    `id`                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`                  VARCHAR(150)    NOT NULL,
    `nif`                   VARCHAR(20)     DEFAULT NULL,
    `tipo`                  ENUM('fabricante','distribuidor','assistencia_tecnica','consumiveis','outro') DEFAULT 'outro',
    `telefone`              VARCHAR(20)     DEFAULT NULL,
    `email`                 VARCHAR(100)    DEFAULT NULL,
    `morada`                VARCHAR(200)    DEFAULT NULL,
    `website`               VARCHAR(150)    DEFAULT NULL,
    `pessoa_contacto`       VARCHAR(100)    DEFAULT NULL,
    `telefone_contacto`     VARCHAR(20)     DEFAULT NULL,
    `observacoes`           TEXT            DEFAULT NULL,
    `created_at`            DATETIME        DEFAULT NOW(),
    `deleted_at`            DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: equipamentos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `equipamentos` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `codigo_inventario` VARCHAR(50)     NOT NULL UNIQUE,
    `designacao`        VARCHAR(150)    NOT NULL,
    `categoria`         ENUM('monitorizacao','suporte_vida','terapia','diagnostico','laboratorio','esterilizacao','reabilitacao','outro') DEFAULT 'outro',
    `marca`             VARCHAR(100)    DEFAULT NULL,
    `modelo`            VARCHAR(100)    DEFAULT NULL,
    `numero_serie`      VARCHAR(100)    DEFAULT NULL,
    `fabricante`        VARCHAR(100)    DEFAULT NULL,
    `data_aquisicao`    DATE            DEFAULT NULL,
    `ano_fabrico`       YEAR            DEFAULT NULL,
    `custo_aquisicao`   DECIMAL(10,2)   DEFAULT NULL,
    `tipo_entrada`      ENUM('compra','doacao','aluguer','emprestimo') DEFAULT 'compra',
    `estado`            ENUM('ativo','manutencao','inativo','calibracao','quarentena','abatido') DEFAULT 'ativo',
    `criticidade`       ENUM('baixa','media','alta','suporte_vida') DEFAULT 'media',
    `id_localizacao`    INT UNSIGNED    DEFAULT NULL,
    `observacoes`       TEXT            DEFAULT NULL,
    `created_at`        DATETIME        DEFAULT NOW(),
    `deleted_at`        DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: equipamento_fornecedor (relação N:M)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `equipamento_fornecedor` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_equipamento`   INT UNSIGNED NOT NULL,
    `id_fornecedor`    INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_fornecedor`)  REFERENCES `fornecedores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: documentos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documentos` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `id_equipamento`   INT UNSIGNED    NOT NULL,
    `tipo`             ENUM('manual_utilizador','manual_servico','certificado_calibracao','contrato_manutencao','fatura','declaracao_conformidade','relatorio_tecnico','outro') DEFAULT 'outro',
    `nome`             VARCHAR(200)    NOT NULL,
    `data_documento`   DATE            DEFAULT NULL,
    `data_validade`    DATE            DEFAULT NULL,
    `ficheiro`         VARCHAR(300)    DEFAULT NULL,
    `observacoes`      TEXT            DEFAULT NULL,
    `created_at`       DATETIME        DEFAULT NOW(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: garantias
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `garantias` (
    `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `id_equipamento`     INT UNSIGNED    NOT NULL,
    `data_inicio`        DATE            DEFAULT NULL,
    `data_fim`           DATE            DEFAULT NULL,
    `tem_contrato`       TINYINT(1)      DEFAULT 0,
    `tipo_contrato`      VARCHAR(100)    DEFAULT NULL,
    `entidade_responsavel` VARCHAR(150)  DEFAULT NULL,
    `periodicidade`      VARCHAR(100)    DEFAULT NULL,
    `observacoes`        TEXT            DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: conteudos_publicos (para backoffice da área pública)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conteudos_publicos` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `chave`     VARCHAR(100)    NOT NULL UNIQUE,
    `valor`     TEXT            DEFAULT NULL,
    `updated_at` DATETIME       DEFAULT NOW() ON UPDATE NOW(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Dados iniciais: agentes
-- --------------------------------------------------------
TRUNCATE `agentes`;
INSERT INTO `agentes` VALUES
(0, AES_ENCRYPT('admin@meditrack.pt',   'Vduu47qL51hLn6bkYkY6NlO1nivsmdfD'), '$2y$10$aia',  'admin', NULL, NOW(), NULL),
(0, AES_ENCRYPT('agente1@meditrack.pt', 'Vduu47qL51hLn6bkYkY6NlO1nivsmdfD'), '$2y$10$O',    'agent', NULL, NOW(), NULL),
(0, AES_ENCRYPT('agente2@meditrack.pt', 'Vduu47qL51hLn6bkYkY6NlO1nivsmdfD'), '$2y$10$B5',   'agent', NULL, NOW(), NULL);

-- --------------------------------------------------------
-- Dados iniciais: conteúdos públicos
-- --------------------------------------------------------
INSERT INTO `conteudos_publicos` (`chave`, `valor`) VALUES
('hero_titulo',      'Inventário médico inteligente e centralizado'),
('hero_descricao',   'A MediTrack transforma a gestão dispersa em folhas de Excel numa plataforma web centralizada, segura e fácil de usar.'),
('sobre_texto',      'A MediTrack foi desenvolvida para centralizar toda a informação numa plataforma web intuitiva, permitindo que as equipas hospitalares se foquem no que realmente importa: o cuidado ao doente.'),
('contacto_morada',  'Rua Dr. António Bernardino de Almeida, 431, 4249-015 Porto'),
('contacto_telefone','+ 351 222 000 000'),
('contacto_email',   'info@meditrack.pt'),
('contacto_horario', 'Segunda a Sexta, 09h00 – 18h00');

-- --------------------------------------------------------
-- Dados de exemplo: localizações
-- --------------------------------------------------------
INSERT INTO `localizacoes` (`edificio`, `piso`, `servico`, `sala`) VALUES
('Edifício Principal', 'Piso 1', 'Unidade de Cuidados Intensivos', 'UCI-A'),
('Edifício Principal', 'Piso 0', 'Urgência', 'Sala de Reanimação'),
('Edifício Principal', 'Piso 2', 'Serviço de Medicina', 'Enfermaria 2A'),
('Edifício Principal', 'Piso 3', 'Serviço de Cirurgia', 'Bloco Operatório 1'),
('Edifício B',         'Piso 1', 'Pediatria', 'Sala de Observação'),
('Edifício B',         'Piso 0', 'Laboratório', 'Lab. Análises Clínicas');

-- --------------------------------------------------------
-- Dados de exemplo: fornecedores
-- --------------------------------------------------------
INSERT INTO `fornecedores` (`nome`, `nif`, `tipo`, `telefone`, `email`, `morada`, `website`, `pessoa_contacto`) VALUES
('Philips Healthcare Portugal', '500123456', 'fabricante',          '222 100 200', 'saude@philips.pt',  'Av. da Liberdade, 110, Lisboa',       'www.philips.pt',   'João Ferreira'),
('Dräger Portugal',             '500234567', 'fabricante',          '222 200 300', 'info@draeger.pt',   'Rua de Entrecampos, 4, Lisboa',       'www.draeger.com',  'Ana Silva'),
('MedEquip Lda',                '509876543', 'distribuidor',        '222 300 400', 'geral@medequip.pt', 'Rua do Ouro, 50, Porto',              'www.medequip.pt',  'Carlos Sousa'),
('TechMed Assistência',         '508765432', 'assistencia_tecnica', '222 400 500', 'apoio@techmed.pt',  'Av. dos Aliados, 200, Porto',         'www.techmed.pt',   'Sofia Costa'),
('B. Braun Portugal',           '500345678', 'fabricante',          '222 500 600', 'info@bbraun.pt',    'Quinta da Fonte, Paço de Arcos',      'www.bbraun.pt',    'Miguel Oliveira');

-- --------------------------------------------------------
-- Dados de exemplo: equipamentos
-- --------------------------------------------------------
INSERT INTO `equipamentos` (`codigo_inventario`, `designacao`, `categoria`, `marca`, `modelo`, `numero_serie`, `fabricante`, `data_aquisicao`, `ano_fabrico`, `custo_aquisicao`, `tipo_entrada`, `estado`, `criticidade`, `id_localizacao`) VALUES
('MT-2022-001', 'Monitor Multiparamétrico de Sinais Vitais', 'monitorizacao', 'Philips',  'IntelliVue MP5',   'MP5-2022-45873',   'Philips Healthcare', '2022-03-15', 2022, 12500.00, 'compra', 'ativo',      'alta',         1),
('MT-2021-002', 'Ventilador Pulmonar',                       'suporte_vida',  'Dräger',   'Evita V500',       'EV500-2021-9934',  'Dräger',             '2021-06-20', 2021, 35000.00, 'compra', 'ativo',      'suporte_vida', 1),
('MT-2020-003', 'Bomba de Infusão',                          'terapia',       'B. Braun', 'Infusomat Space',  'INF-2020-88321',   'B. Braun',           '2020-11-10', 2020,  4200.00, 'compra', 'ativo',      'media',        3),
('MT-2021-004', 'Desfibrilhador',                            'suporte_vida',  'Zoll',     'R Series',         'ZR-2021-7712',     'Zoll Medical',       '2021-09-05', 2021, 18000.00, 'compra', 'ativo',      'alta',         2),
('MT-2023-005', 'Oxímetro de Pulso',                         'monitorizacao', 'Nonin',    'Model 7500',       'NNP-2023-1234',    'Nonin Medical',      '2023-01-18', 2023,   850.00, 'compra', 'ativo',      'media',        5),
('MT-2019-006', 'Autoclave',                                 'esterilizacao', 'Tuttnauer','3870EHS',          'TAE-2019-5566',    'Tuttnauer',          '2019-04-22', 2019,  8900.00, 'compra', 'manutencao', 'baixa',        4),
('MT-2022-007', 'Eletrocardiógrafo',                         'diagnostico',   'Schiller', 'AT-2 Plus',        'SCH-2022-9981',    'Schiller',           '2022-07-30', 2022,  3200.00, 'compra', 'ativo',      'alta',         3);
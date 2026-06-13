-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Tempo de geração: 13-Jun-2026 às 10:57
-- Versão do servidor: 8.0.40
-- versão do PHP: 8.3.14

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `meditrack`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `agentes`
--

CREATE TABLE `agentes` (
  `id` int UNSIGNED NOT NULL,
  `name` varbinary(200) DEFAULT NULL,
  `passwrd` varchar(200) DEFAULT NULL,
  `profile` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `agentes`
--

INSERT INTO `agentes` (`id`, `name`, `passwrd`, `profile`, `last_login`, `created_at`, `deleted_at`) VALUES
(1, 0xf2b7e6799d346e41e2bef7444101ed3b07c54fe6fa1c44d9e8d3b0cd6c2aca79, '$2y$10$BNkhJGfDxFK4wb1GUqgoSuXOuQxnCs40g1uqSjy2W/UNbg25wmatm', 'admin', '2026-06-12 15:17:56', '2026-04-13 11:59:35', NULL),
(2, 0x946cfbd454eb660acc81f7c1466fe6d1533f504fa94241273b9c6b5482f9b809, '$2y$10$77ZO/TO8IASoXiFBK09L8ud3fEx2SSffU4vqgz/.xdnoIizVKLxvG', 'agent', '2026-06-07 10:37:42', '2026-04-13 11:59:35', NULL),
(3, 0xcb3e01f542e5198c9fa757514478f921533f504fa94241273b9c6b5482f9b809, '$2y$10$77ZO/TO8IASoXiFBK09L8ud3fEx2SSffU4vqgz/.xdnoIizVKLxvG', 'agent', NULL, '2026-04-13 11:59:35', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `componentes`
--

CREATE TABLE `componentes` (
  `id` int UNSIGNED NOT NULL,
  `id_equipamento` int UNSIGNED NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `designacao` varchar(150) NOT NULL,
  `quantidade` int DEFAULT '1',
  `numero_serie` varchar(100) DEFAULT NULL,
  `estado` enum('ativo','inativo','substituido') DEFAULT 'ativo',
  `observacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `componentes`
--

INSERT INTO `componentes` (`id`, `id_equipamento`, `codigo`, `designacao`, `quantidade`, `numero_serie`, `estado`, `observacoes`, `created_at`) VALUES
(1, 7, 'MT-2026-001.01', 'Monitor', 1, 'RNGHIO', 'ativo', NULL, '2026-04-15 10:33:49'),
(2, 12, 'MT-2022-001.01', 'Sensor de oximetria (SpO₂)', 2, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(3, 12, 'MT-2022-001.02', 'Cabo ECG', 1, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(4, 12, 'MT-2022-001.03', 'Manguito de pressão arterial não invasiva (NIBP)', 1, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(5, 12, 'MT-2022-001.04', 'Sensor de temperatura', 1, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(6, 12, 'MT-2022-001.05', 'Bateria', 2, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(7, 15, 'MT-2021-004.01', 'Pás de desfibrilhação', 1, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(8, 15, 'MT-2021-004.02', 'Cabos ECG', 2, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(9, 15, 'MT-2021-004.03', 'Bateria', 1, NULL, 'ativo', NULL, '2026-04-15 10:45:11'),
(10, 15, 'MT-2021-004.04', 'Impressora térmica', 1, NULL, 'ativo', NULL, '2026-04-15 10:45:11');

-- --------------------------------------------------------

--
-- Estrutura da tabela `contactos`
--

CREATE TABLE `contactos` (
  `id` int UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `instituicao` varchar(150) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `assunto` varchar(150) NOT NULL,
  `mensagem` text NOT NULL,
  `lido` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `contactos`
--

INSERT INTO `contactos` (`id`, `nome`, `instituicao`, `email`, `telefone`, `assunto`, `mensagem`, `lido`, `created_at`) VALUES
(7, 'Sofia Tabuada', 'Hospital da Luz', 'sofiatabuada@gmail.com', '987654321', 'Pedido de demonstração', 'kjbvuu', 0, '2026-06-06 15:20:03'),
(8, 'Pedro Gomes', 'Trofa Saúde', 'pedrogomes@gmail.com', '918363567', 'Pedido de demonstração', 'webnbfibuwef', 0, '2026-06-07 10:38:36');

-- --------------------------------------------------------

--
-- Estrutura da tabela `conteudos_publicos`
--

CREATE TABLE `conteudos_publicos` (
  `id` int UNSIGNED NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `conteudos_publicos`
--

INSERT INTO `conteudos_publicos` (`id`, `chave`, `valor`, `updated_at`) VALUES
(1, 'hero_titulo', 'Inventário médico inteligente e centralizado', '2026-04-14 22:51:15'),
(2, 'hero_descricao', 'A MediTrack transforma a gestão dispersa em folhas de Excel numa plataforma web centralizada, segura e fácil de usar.', '2026-04-14 22:51:15'),
(3, 'sobre_texto', 'A MediTrack foi desenvolvida para centralizar toda a informação numa plataforma web intuitiva, permitindo que as equipas hospitalares se foquem no que realmente importa: o cuidado ao doente.', '2026-04-14 22:51:15'),
(4, 'contacto_morada', 'Rua Dr. António Bernardino de Almeida, 431 4249-015 Porto', '2026-04-14 22:35:44'),
(5, 'contacto_telefone', '+351 222 000 000', '2026-04-14 22:35:44'),
(6, 'contacto_email', 'info@meditrack.pt', '2026-04-24 14:17:49'),
(7, 'contacto_horario', 'Segunda a Sexta, 09h00 – 18h00', '2026-04-14 22:35:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `documentos`
--

CREATE TABLE `documentos` (
  `id` int UNSIGNED NOT NULL,
  `id_equipamento` int UNSIGNED NOT NULL,
  `tipo` enum('manual_utilizador','manual_servico','certificado_calibracao','contrato_manutencao','fatura','declaracao_conformidade','relatorio_tecnico','outro') DEFAULT 'outro',
  `nome` varchar(200) NOT NULL,
  `data_documento` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `ficheiro` varchar(300) DEFAULT NULL,
  `observacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `documentos`
--

INSERT INTO `documentos` (`id`, `id_equipamento`, `tipo`, `nome`, `data_documento`, `data_validade`, `ficheiro`, `observacoes`, `created_at`) VALUES
(1, 3, 'relatorio_tecnico', '2ljbefdo2efpbi', '2026-04-14', '2028-04-14', 'lqwndnqwd', NULL, '2026-04-14 16:08:34'),
(2, 6, 'relatorio_tecnico', '2ljbefdo2efpbi', '2026-04-14', '2031-04-14', 'doc_69debbeb614e2.pdf', NULL, '2026-04-14 23:12:59'),
(4, 14, 'manual_utilizador', 'Manual de Utilização', '2026-04-17', '2027-04-17', '_2025_2026__Guia_de_Submissa__o_v0202.pdf', NULL, '2026-04-17 13:17:36'),
(5, 17, 'manual_utilizador', 'Manual de Utilizador', '2025-06-04', '2030-06-04', 'doc_6a2137bbdd3d0.pdf', NULL, '2026-06-04 09:30:51'),
(6, 18, 'certificado_calibracao', 'Certificado de Calibração', '2021-11-08', '2026-06-30', 'doc_6a2542e7051fd.pdf', NULL, '2026-06-07 11:07:35');

-- --------------------------------------------------------

--
-- Estrutura da tabela `emprestimos`
--

CREATE TABLE `emprestimos` (
  `id` int UNSIGNED NOT NULL,
  `id_equipamento` int UNSIGNED NOT NULL,
  `id_localizacao_origem` int UNSIGNED DEFAULT NULL,
  `servico_destino` varchar(150) NOT NULL,
  `responsavel` varchar(100) DEFAULT NULL,
  `data_saida` date NOT NULL,
  `data_retorno_prevista` date DEFAULT NULL,
  `data_retorno_real` datetime DEFAULT NULL,
  `observacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `emprestimos`
--

INSERT INTO `emprestimos` (`id`, `id_equipamento`, `id_localizacao_origem`, `servico_destino`, `responsavel`, `data_saida`, `data_retorno_prevista`, `data_retorno_real`, `observacoes`, `created_at`) VALUES
(1, 15, 6, 'Urgencia', 'mariana', '2026-04-24', '2026-04-25', '2026-04-24 15:20:04', NULL, '2026-04-24 15:20:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipamentos`
--

CREATE TABLE `equipamentos` (
  `id` int UNSIGNED NOT NULL,
  `codigo_inventario` varchar(50) NOT NULL,
  `designacao` varchar(150) NOT NULL,
  `categoria` enum('monitorizacao','suporte_vida','terapia','diagnostico','laboratorio','esterilizacao','reabilitacao','outro') DEFAULT 'outro',
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `fabricante` varchar(100) DEFAULT NULL,
  `data_aquisicao` date DEFAULT NULL,
  `ano_fabrico` year DEFAULT NULL,
  `custo_aquisicao` decimal(10,2) DEFAULT NULL,
  `tipo_entrada` enum('compra','doacao','aluguer','emprestimo') DEFAULT 'compra',
  `estado` enum('ativo','manutencao','inativo','calibracao','quarentena','abatido') DEFAULT 'ativo',
  `criticidade` enum('baixa','media','alta','suporte_vida') DEFAULT 'media',
  `id_localizacao` int UNSIGNED DEFAULT NULL,
  `observacoes` text,
  `assistencia_nome` varchar(255) DEFAULT NULL,
  `assistencia_telefone` varchar(50) DEFAULT NULL,
  `assistencia_email` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `equipamentos`
--

INSERT INTO `equipamentos` (`id`, `codigo_inventario`, `designacao`, `categoria`, `marca`, `modelo`, `numero_serie`, `fabricante`, `data_aquisicao`, `ano_fabrico`, `custo_aquisicao`, `tipo_entrada`, `estado`, `criticidade`, `id_localizacao`, `observacoes`, `assistencia_nome`, `assistencia_telefone`, `assistencia_email`, `created_at`, `deleted_at`) VALUES
(3, 'MT-2024-01', 'Monitor', 'monitorizacao', 'Phylips', 'uywebu', '0987', 'skjdhbc', '2026-04-14', '2026', 1000.00, 'compra', 'ativo', 'media', NULL, NULL, NULL, NULL, NULL, '2026-04-14 16:07:45', '2026-04-15 10:31:13'),
(4, 'MT-2024-02', 'Ressonancia magnetica', 'diagnostico', 'Siemens', 'kjewnfjb', '2io98r', 'Siemens', '2026-04-13', '2026', 10000.00, 'compra', 'manutencao', 'alta', NULL, NULL, NULL, NULL, NULL, '2026-04-14 16:15:13', '2026-04-15 10:31:15'),
(5, 'MT-2024-03', 'Ventilador mecânico', 'suporte_vida', 'Medtronic', 'oebobeovu', '089u23eh', 'Medtronic', '2026-04-13', '2026', 12000.00, 'compra', 'inativo', 'suporte_vida', NULL, NULL, NULL, NULL, NULL, '2026-04-14 16:46:08', '2026-04-15 10:31:16'),
(6, 'MT-2024-04', 'BiPAP', 'monitorizacao', 'Phylips', 'KJNRFV', 'RNGHIO', 'Phylips', '2026-04-12', '2026', 5000.00, 'compra', 'ativo', 'media', NULL, NULL, NULL, NULL, NULL, '2026-04-14 16:48:21', '2026-04-15 10:31:18'),
(7, 'MT-2026-001', 'Ressonancia magnetica', 'suporte_vida', 'Phylips', 'kjewnfjb', 'RNGHIO', 'Medtronic', '2026-04-15', '2026', 10000.00, 'compra', 'ativo', 'media', NULL, NULL, NULL, NULL, NULL, '2026-04-15 10:33:34', '2026-04-15 10:34:19'),
(12, 'MT-2022-001', 'Monitor Multiparamétrico de Sinais Vitais', 'monitorizacao', 'Philips', 'IntelliVue MP5', 'MP5-2022-45873', 'Philips Healthcare', '2022-03-15', '2022', 12500.00, 'compra', 'ativo', 'suporte_vida', 5, NULL, NULL, NULL, NULL, '2026-04-15 10:44:10', NULL),
(13, 'MT-2021-002', 'Ventilador Pulmonar', 'suporte_vida', 'Dräger', 'Evita V500', 'EV500-2021-9934', 'Dräger', '2021-06-20', '2021', 35000.00, 'compra', 'ativo', 'suporte_vida', 5, NULL, 'TechMed Serviçoes', '938765436', NULL, '2026-04-15 10:44:10', NULL),
(14, 'MT-2020-003', 'Bomba de Infusão', 'terapia', 'B. Braun', 'Infusomat Space', 'INF-2020-88321', 'B. Braun', '2020-11-10', '2020', 4200.00, 'compra', 'manutencao', 'media', 9, NULL, 'João Silva', '91234567', NULL, '2026-04-15 10:44:10', NULL),
(15, 'MT-2021-004', 'Desfibrilhador', 'suporte_vida', 'Zoll', 'R Series', 'ZR-2021-7712', 'Zoll Medical', '2021-09-05', '2021', 18000.00, 'compra', 'inativo', 'alta', 6, NULL, 'Sofia Tabuada', '914587264', 'sofiatabuada@gmail.com', '2026-04-15 10:44:10', NULL),
(16, 'MT-2026-002', 'Electroencefalógrafo', 'diagnostico', 'Natus', 'Xltek', 'XLT-2024-3391', 'Natus Medical', '2026-06-03', '2026', 22.00, 'compra', 'manutencao', 'alta', 11, NULL, 'Margarida Pereira', '928765432', NULL, '2026-06-03 16:07:44', NULL),
(17, 'MT-2026-003', 'Monitor Multiparamétrico Bedside', 'monitorizacao', 'Philips', 'IntelliVue MP70', 'PH-MP70-123456', 'Philips Healthcare', '2026-06-04', '2025', 15.50, 'compra', 'ativo', 'suporte_vida', 5, 'Monitor de sinais vitais com display de 15 polegadas, capacidade de monitorização de até 8 pacientes em rede.', 'Philips Healthcare Support Portugal', '210000000', 'support@philips.pt', '2026-06-04 09:29:29', NULL),
(18, 'MT-2026-004', 'Ecógrafo Portátil', 'diagnostico', 'GE Healthcare', 'Vivid iq', 'GEVIQ-44871-21', 'GE Healthcare', '2021-11-08', '2021', 15.20, 'compra', 'ativo', 'media', 12, NULL, 'GE Healthcare Portugal', '214 251 300', 'servico.pt@gehealthcare.com', '2026-06-07 11:07:35', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipamento_fornecedor`
--

CREATE TABLE `equipamento_fornecedor` (
  `id` int UNSIGNED NOT NULL,
  `id_equipamento` int UNSIGNED NOT NULL,
  `id_fornecedor` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `equipamento_fornecedor`
--

INSERT INTO `equipamento_fornecedor` (`id`, `id_equipamento`, `id_fornecedor`) VALUES
(1, 3, 2),
(2, 4, 3),
(4, 6, 2),
(5, 5, 4),
(6, 7, 2),
(7, 12, 5),
(15, 17, 5),
(16, 13, 6),
(18, 14, 7),
(19, 15, 8),
(20, 16, 10);

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `nif` varchar(20) DEFAULT NULL,
  `tipo` enum('fabricante','distribuidor','assistencia_tecnica','consumiveis','outro') DEFAULT 'outro',
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `morada` varchar(200) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `pessoa_contacto` varchar(100) DEFAULT NULL,
  `telefone_contacto` varchar(20) DEFAULT NULL,
  `observacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id`, `nome`, `nif`, `tipo`, `telefone`, `email`, `morada`, `website`, `pessoa_contacto`, `telefone_contacto`, `observacoes`, `created_at`, `deleted_at`) VALUES
(1, 'khjviiyv', '246796120', 'fabricante', '987654321', 'sofiatabuada@gmail.com', '8jgycvnjkjhgfder4567uj', NULL, 'Sofia Tabuada', '123456789', NULL, '2026-04-14 12:59:23', '2026-04-14 12:59:47'),
(2, 'phylips', '1234567', 'fabricante', '987654321', 'sofiatabuada@gmail.com', NULL, NULL, 'Sofia Tabuada', '123456789', NULL, '2026-04-14 16:09:17', '2026-04-15 10:34:34'),
(3, 'Siemens', '1234567', 'fabricante', '987654321', 'sofiatabuada@gmail.com', 'qkjbwdjqeofpinqe', NULL, 'Sofia Tabuada', '123456678', NULL, '2026-04-14 16:16:48', '2026-04-15 10:34:36'),
(4, 'Medtronic', '9283764', 'fabricante', '9827648', 'sofiatabuada@gmail.com', 'ejhrf vif', 'jdefbv', 'Sofia Tabuada', 'o289870932', NULL, '2026-04-14 16:46:40', '2026-04-15 10:34:32'),
(5, 'Philips Healthcare Portugal', '500123456', 'fabricante', '222 100 200', 'saude@philips.pt', 'Av. da Liberdade, 110, Lisboa', 'www.philips.pt', 'João Ferreira', NULL, NULL, '2026-04-15 10:40:36', NULL),
(6, 'Dräger Portugal', '500234567', 'fabricante', '222 200 300', 'info@draeger.pt', 'Rua de Entrecampos, 4, Lisboa', 'www.draeger.com', 'Ana Silva', NULL, NULL, '2026-04-15 10:40:36', NULL),
(7, 'B. Braun Portugal', '500345678', 'fabricante', '222 500 600', 'info@bbraun.pt', 'Quinta da Fonte, Paço de Arcos', 'www.bbraun.pt', 'Miguel Oliveira', NULL, NULL, '2026-04-15 10:40:36', NULL),
(8, 'Zoll Medical Portugal', '500456789', 'fabricante', '222 600 700', 'info@zoll.pt', 'Av. dos Aliados, 200, Porto', 'www.zoll.com', 'Sofia Costa', NULL, NULL, '2026-04-15 10:40:36', NULL),
(9, 'TechMed Assistência', '508765432', 'assistencia_tecnica', '222 400 500', 'apoio@techmed.pt', 'Av. dos Aliados, 200, Porto', 'www.techmed.pt', 'Carlos Sousa', NULL, NULL, '2026-04-15 10:40:36', '2026-06-05 14:15:37'),
(10, 'Natus Medical Portugal', '500987654', 'distribuidor', '222 700 800', 'info@natus.pt', 'Av. da Boavista, 1500, Porto', NULL, 'Ricardo Mendes', '918311468', NULL, '2026-06-03 16:07:38', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `garantias`
--

CREATE TABLE `garantias` (
  `id` int UNSIGNED NOT NULL,
  `id_equipamento` int UNSIGNED NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `tem_contrato` tinyint(1) DEFAULT '0',
  `tipo_contrato` varchar(100) DEFAULT NULL,
  `entidade_responsavel` varchar(150) DEFAULT NULL,
  `periodicidade` varchar(100) DEFAULT NULL,
  `observacoes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `garantias`
--

INSERT INTO `garantias` (`id`, `id_equipamento`, `data_inicio`, `data_fim`, `tem_contrato`, `tipo_contrato`, `entidade_responsavel`, `periodicidade`, `observacoes`) VALUES
(4, 15, '2026-04-17', '2027-04-17', 1, 'Manutençao preventiva e corretiva', 'TechMed assistência', 'Semestral', NULL),
(5, 14, '2026-04-17', '2026-05-17', 0, NULL, NULL, NULL, NULL),
(6, 17, '2026-06-04', '2027-06-04', 1, 'Manutençao preventiva e corretiva', 'TechMed assistência', 'Semestral', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `localizacoes`
--

CREATE TABLE `localizacoes` (
  `id` int UNSIGNED NOT NULL,
  `edificio` varchar(100) DEFAULT NULL,
  `piso` varchar(50) DEFAULT NULL,
  `servico` varchar(100) NOT NULL,
  `sala` varchar(100) DEFAULT NULL,
  `observacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `localizacoes`
--

INSERT INTO `localizacoes` (`id`, `edificio`, `piso`, `servico`, `sala`, `observacoes`, `created_at`) VALUES
(5, 'Edifício Principal', 'Piso 1', 'Unidade de Cuidados Intensivos', 'UCI-A', NULL, '2026-04-15 10:40:36'),
(6, 'Edifício Principal', 'Piso 0', 'Urgência', 'Sala de Reanimação', NULL, '2026-04-15 10:40:36'),
(7, 'Edifício Principal', 'Piso 2', 'Serviço de Medicina', 'Enfermaria 2A', NULL, '2026-04-15 10:40:36'),
(8, 'Edifício Principal', 'Piso 3', 'Serviço de Cirurgia', 'Bloco Operatório 1', NULL, '2026-04-15 10:40:36'),
(9, 'Edifício B', 'Piso 1', 'Pediatria', 'Sala de Observação', NULL, '2026-04-15 10:40:36'),
(10, 'Edifício B', 'Piso 0', 'Laboratório', 'Lab. Análises Clínicas', NULL, '2026-04-15 10:40:36'),
(11, 'Edifício Principal', 'Piso 3', 'Neurologia', 'Sala de Diagnóstico 1', NULL, '2026-06-03 16:54:22'),
(12, 'Edifício Principal', 'Piso 2', 'Cardiologia', 'Sala Diagnóstico 1', NULL, '2026-06-07 10:57:59');

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `data_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(20) NOT NULL,
  `utilizador` varchar(150) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `manutencoes`
--

CREATE TABLE `manutencoes` (
  `id` int UNSIGNED NOT NULL,
  `id_equipamento` int UNSIGNED NOT NULL,
  `tipo` enum('preventiva','corretiva','calibracao','inspecao') NOT NULL,
  `estado` enum('agendada','em_curso','concluida','cancelada') NOT NULL DEFAULT 'agendada',
  `descricao` text,
  `trabalho_realizado` text,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `manutencoes`
--

INSERT INTO `manutencoes` (`id`, `id_equipamento`, `tipo`, `estado`, `descricao`, `trabalho_realizado`, `data_inicio`, `data_fim`, `created_at`) VALUES
(1, 14, 'preventiva', 'agendada', NULL, NULL, '2026-06-06', '2026-06-07', '2026-06-05 19:15:24'),
(2, 16, 'preventiva', 'em_curso', NULL, NULL, '2026-06-07', '2026-06-07', '2026-06-07 11:09:28');

-- --------------------------------------------------------

--
-- Estrutura da tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int UNSIGNED NOT NULL,
  `id_equipamento` int UNSIGNED NOT NULL,
  `id_localizacao_origem` int UNSIGNED DEFAULT NULL,
  `id_localizacao_destino` int UNSIGNED DEFAULT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `observacoes` text,
  `data_movimentacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `registado_por` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `movimentacoes`
--

INSERT INTO `movimentacoes` (`id`, `id_equipamento`, `id_localizacao_origem`, `id_localizacao_destino`, `motivo`, `observacoes`, `data_movimentacao`, `registado_por`) VALUES
(1, 14, 7, 9, NULL, NULL, '2026-04-24 00:00:00', 'admin@meditrack.pt');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `agentes`
--
ALTER TABLE `agentes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `componentes`
--
ALTER TABLE `componentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipamento` (`id_equipamento`);

--
-- Índices para tabela `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `conteudos_publicos`
--
ALTER TABLE `conteudos_publicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices para tabela `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipamento` (`id_equipamento`);

--
-- Índices para tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipamento` (`id_equipamento`),
  ADD KEY `id_localizacao_origem` (`id_localizacao_origem`);

--
-- Índices para tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_localizacao` (`id_localizacao`);

--
-- Índices para tabela `equipamento_fornecedor`
--
ALTER TABLE `equipamento_fornecedor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipamento` (`id_equipamento`),
  ADD KEY `id_fornecedor` (`id_fornecedor`);

--
-- Índices para tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `garantias`
--
ALTER TABLE `garantias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipamento` (`id_equipamento`);

--
-- Índices para tabela `localizacoes`
--
ALTER TABLE `localizacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipamento` (`id_equipamento`);

--
-- Índices para tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipamento` (`id_equipamento`),
  ADD KEY `id_localizacao_origem` (`id_localizacao_origem`),
  ADD KEY `id_localizacao_destino` (`id_localizacao_destino`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agentes`
--
ALTER TABLE `agentes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `componentes`
--
ALTER TABLE `componentes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `conteudos_publicos`
--
ALTER TABLE `conteudos_publicos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `equipamento_fornecedor`
--
ALTER TABLE `equipamento_fornecedor`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `garantias`
--
ALTER TABLE `garantias`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `localizacoes`
--
ALTER TABLE `localizacoes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `componentes`
--
ALTER TABLE `componentes`
  ADD CONSTRAINT `componentes_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD CONSTRAINT `emprestimos_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emprestimos_ibfk_2` FOREIGN KEY (`id_localizacao_origem`) REFERENCES `localizacoes` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  ADD CONSTRAINT `equipamentos_ibfk_1` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `equipamento_fornecedor`
--
ALTER TABLE `equipamento_fornecedor`
  ADD CONSTRAINT `equipamento_fornecedor_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipamento_fornecedor_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `garantias`
--
ALTER TABLE `garantias`
  ADD CONSTRAINT `garantias_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD CONSTRAINT `manutencoes_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`);

--
-- Limitadores para a tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacoes_ibfk_2` FOREIGN KEY (`id_localizacao_origem`) REFERENCES `localizacoes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `movimentacoes_ibfk_3` FOREIGN KEY (`id_localizacao_destino`) REFERENCES `localizacoes` (`id`) ON DELETE SET NULL;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
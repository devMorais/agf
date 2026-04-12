-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 12/04/2026 às 22:27
-- Versão do servidor: 9.5.0
-- Versão do PHP: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agf`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `banner`
--

CREATE TABLE `banner` (
  `id` int NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `cadastrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int NOT NULL,
  `slug` varchar(255) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `texto` text,
  `status` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `slug`, `titulo`, `texto`, `status`) VALUES
(1, 'projetos', 'Projetos', 'Todos os projetos serão cadastrados nesta categoria.', 1),
(2, 'noticias', 'Notícias', 'Todas as notícias serão cadastradas neste espaço.', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacoes`
--

CREATE TABLE `doacoes` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('aguardando','confirmada','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'aguardando',
  `metodo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cadastrado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `infinitepay_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `infinitepay_order_nsu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `infinitepay_transaction_nsu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `infinitepay_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `infinitepay_receipt_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `infinitepay_subscription_status` enum('ativo','cancelado','erro') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `infinitepay_subscription_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doador_nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doador_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doador_cpf` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doador_telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doador_anonimo` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `doacoes`
--

INSERT INTO `doacoes` (`id`, `usuario_id`, `valor`, `status`, `metodo`, `cadastrado_em`, `atualizado_em`, `infinitepay_link`, `infinitepay_order_nsu`, `infinitepay_transaction_nsu`, `infinitepay_slug`, `infinitepay_receipt_url`, `infinitepay_subscription_status`, `infinitepay_subscription_slug`, `doador_nome`, `doador_email`, `doador_cpf`, `doador_telefone`, `doador_anonimo`) VALUES
(1, NULL, 1.00, 'confirmada', 'PIX', '2026-04-12 09:38:16', NULL, 'https://checkout.infinitepay.io/rosivalmorais?lenc=G_0AYIzEOCZyTFxRSxBDWR_b9iNlTRo6o9TD_CEaGSERsn7RkK8k3rb_vVddCg7BIdJ4QfzOaN3Ore5Kb1iegyUz_ROdCIEeJK_bmwvLsSBph7y3d4U66qw0Q2VF9U6vLfPtCM7sVFb3OfPPJeqw6ElXkBj0UpBZ_mxYGX2nkXWIZn8auHlH27yqdTakrdKL_RO1rMD3p1CFZCqFpckpb6BoZ-_ZIZBMawvMMT4qUe5HeVTLwhoB.v1.6f6d63012d63f805', 'doacao-1', '86dce99a-3bf0-48c1-8d29-887aae961bde', 'xEXsKjdqL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, NULL, 1.00, 'confirmada', 'PIX', '2026-04-12 10:20:43', NULL, 'https://checkout.infinitepay.io/rosivalmorais?lenc=G2YBQORibkdqIt7wRsi7n9lP0IhnQnaNWhJTN91fcdHYrW1tagtEmmiXUjq3UMgM_TmzGLz-eEXS9Gd7idf117HANQQwrDQYMJ_BWkMDBHOuQPNkwSio1USeS8FHEBPS2AEyAqY9BFKp9JFdJmOxbCp3gaUEiQ9CNRsrGoECDMUXg7zuDTSFJsgKGBIin_RX47aWedN3etvdP39PGeG3_E3RGrJaAePAtvzXKii_vSzAYjzyzANpPGCjvjOEeyAahvx3_Eg1BY51wmARlnFEA_Xf1DpG4ssWQ_KIUne6BuwkBfOIoLKCKiRjDTo.v1.a0e4edae5718ab35', 'doacao-2', '963dbc92-33ed-4cf8-9620-f56d0ebe6caa', '7dt5Mu2GxL', NULL, NULL, NULL, 'Fernando Aguiar da Costa Morais', 'farguiarn3@gmail.com.br', NULL, '61983411859', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos`
--

CREATE TABLE `enderecos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `cep` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `logradouro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bairro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cidade` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cadastrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `enderecos`
--

INSERT INTO `enderecos` (`id`, `usuario_id`, `cep`, `logradouro`, `bairro`, `cidade`, `estado`, `cadastrado_em`, `atualizado_em`) VALUES
(1, 1, '73755214', 'quadra-214-casa-03', 'Jardim Paquetá II', 'Planaltina', 'GO', '2024-03-04 00:21:04', NULL),
(2, 94, '73755231', 'quadra-231-casa-7', 'Jardim Paquetá II', 'Planaltina', 'GO', '2025-08-09 03:46:04', NULL),
(3, 95, '73756014', 'quadra-14-lote-05', 'Jardim das Paineiras', 'Planaltina', 'GO', '2025-08-09 12:30:02', NULL),
(4, 118, '00000-000', 'Qa 18 Mr casa 01a setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(5, 121, '00000-000', 'Quadra 219 cs 08 paqueta ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(6, 122, '00000-000', 'Jardim paqueta quadra 83 casa 22', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(7, 123, '00000-000', 'Quadra 4 MC casa 10 serto leste', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(8, 124, '00000-000', 'Quadra 13 Mr 3 casa 13 setor Norte ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(9, 125, '00000-000', 'Residencial caic lote 356 d setor de mansões oeste planaltina Goiás ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(10, 126, '00000-000', 'Qa10mr casa 09 setor Oeste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(11, 127, '00000-000', 'Qd 212 casa 13 jardim Paqueta', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(12, 128, '00000-000', 'Qa 12 MR lote 13-B setor norte', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(13, 129, '00000-000', 'Q8 Mr 1 casa 11 setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(14, 130, '00000-000', 'Q8 mr 1 casa12 setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(15, 131, '00000-000', 'Q8 Mr 1 casa 12 setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(16, 132, '00000-000', 'Quadra 08 Mr 15 casa 10 setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(17, 133, '00000-000', 'Quadra 177 lote 4 jardim Paquetá ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(18, 134, '00000-000', 'Condomínio Santorine barrolandia ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(19, 135, '00000-000', 'Quadra 3 mr 9 casa 22 setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(20, 136, '00000-000', 'Quadra 12 mr 6 casa 17 setor oeste', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(21, 137, '00000-000', 'Q a 22 mc casa 01 setor leste', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(22, 138, '00000-000', '119 rua 42 casa 09 brasilinha leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(23, 139, '00000-000', 'Quadra 9m2 casa 26 setor norte ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(24, 140, '00000-000', 'Q QUADRA 125 RUA 42 LT 17', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(25, 141, '00000-000', 'Quadra 2 MR 1,  16 A', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(26, 142, '00000-000', 'Expansão São José casas 137', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(27, 143, '00000-000', 'Quadra 5 lote 23 Jardim  sao Francisco', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(28, 144, '00000-000', 'Qd21 cs91 setor aeroporto mutirão ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(29, 145, '00000-000', 'Estação São José casa 68', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(30, 146, '00000-000', 'Quadra 6 Mr 13 lote 12 apartamento 102 setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(31, 147, '00000-000', 'Qd242 casa 01 lote 1 jardim paqueta ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(32, 148, '00000-000', 'Quadra 28 casa 06 bairro imigrantes ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(33, 149, '00000-000', 'Quadra 12 casa 69 mutirão ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(34, 150, '00000-000', 'Qd 01 mr 03 casa 03 st oeste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(35, 151, '00000-000', 'Qd 01 mr 03 casa 03 st oeste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(36, 152, '00000-000', 'Quadra 13 casa 54 setor aeroporto etapa 1', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(37, 153, '00000-000', 'Quadra 5 mr 8 casa 11 setor norte', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(38, 154, '00000-000', 'Qd7casa11saojose', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(39, 155, '00000-000', 'Q 2 Mr 5 casa 29 setor oeste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(40, 156, '00000-000', 'Qd 21 cs 91 setor aeroporto mutirão ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(41, 157, '00000-000', 'RUA 3 QUADRA 4 CASA 16 Mutirao Planaltina Goiás ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(42, 158, '00000-000', 'Qd 21 cs 91 setor aeroporto mutirão ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(43, 159, '00000-000', 'Avenida  centro Q9 L57', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(44, 160, '00000-000', 'Avenida  centro Q9 L57', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(45, 161, '00000-000', 'Quadra 2 MR 3', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(46, 162, '00000-000', 'Quadra 1 lote 15 imigrantes ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(47, 163, '00000-000', 'Qd 07 mr13 LT 02 setor Sul atrás do residencial gangah ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(48, 164, '00000-000', 'Q 23 casa 6 A bairro São José dois ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(49, 165, '00000-000', 'Quando 03 Mr 10 casa 7 setor Norte Planaltina Goiás ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(50, 166, '00000-000', 'QI 08 MI LOTE 09 Setor de oficina sul: Planaltina de Goiás ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(51, 167, '00000-000', 'Quadra 218 lote 15 b jardim Paquetá ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(52, 168, '00000-000', 'Quadra 05 mr 08 casa 05 setor oeste', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(53, 169, '00000-000', 'Quadra 3 MR 10', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(54, 170, '00000-000', 'Rua128quadra 346 casa 02 lote 09', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(55, 171, '00000-000', 'Quadra 8 Mr 16 casa 5 setor leste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(56, 172, '00000-000', 'Quadra 2 mr 7 casa 11 setor oeste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(57, 173, '00000-000', 'Quadra1 casa 10 vila mutirão planaltina de Goiás ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(58, 174, '00000-000', 'Qr8 mr 5 casa 6 setor leste', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(59, 175, '00000-000', 'Q 5 MR 4 CASA 1 Setor Leste', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(60, 176, '00000-000', 'Q 5 MR 4 CASA 1 Setor Leste', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(61, 177, '00000-000', 'Q4 mr8 casa 4 setor sul ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(62, 178, '00000-000', 'Quadra 1 mr 5 casa 11 Setor norte', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(63, 179, '00000-000', 'Q 12 mr 05 casa 30 setor oeste ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(64, 180, '00000-000', 'Qd 11 lt 12 jardim das paineiras ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(65, 181, '00000-000', 'Quadra 15 mr 3 casa 38 setor norte ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(66, 182, '00000-000', 'Qd 08 mr 16 casa 09 setor leste última rua dps buracão ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(67, 183, '00000-000', 'Quadra 190 Lote 3 Barrolândia Setor Aeroporto', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(68, 184, '00000-000', 'Quadra 190 Lote 3 Barrolândia ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(69, 185, '00000-000', 'Quadra 190 Lote 3 Barrolândia Setor Aeroporto', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(70, 186, '00000-000', 'Quadra 4 casa 92 setor aeroporto multirão ', NULL, NULL, NULL, '2026-02-21 00:46:21', NULL),
(71, 188, '00000-000', 'Quadra 73 Casa 05', NULL, NULL, NULL, '2026-03-03 19:17:24', NULL),
(72, 189, '00000-000', 'Jardim das Paineiras', NULL, NULL, NULL, '2026-04-11 18:50:02', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagens`
--

CREATE TABLE `imagens` (
  `id` int NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `imagem` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cadastrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `imagens`
--

INSERT INTO `imagens` (`id`, `id_usuario`, `imagem`, `slug`, `cadastrado_em`, `atualizado_em`) VALUES
(13, 95, 'uploads/imagens/usuarios/whatsapp-image-2025-08-09-at-09-47-10-jpeg_27.jpg', NULL, '2025-08-09 23:56:33', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_pagamento_infinitepay`
--

CREATE TABLE `logs_pagamento_infinitepay` (
  `id` int NOT NULL,
  `doacao_id` int DEFAULT NULL,
  `etapa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mensagem` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `codigo_erro` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `request_data` json DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `infinitepay_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `infinitepay_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_nsu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_nsu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `endpoint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `http_code` int DEFAULT NULL,
  `ip_usuario` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `cadastrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `logs_pagamento_infinitepay`
--

INSERT INTO `logs_pagamento_infinitepay` (`id`, `doacao_id`, `etapa`, `status`, `mensagem`, `codigo_erro`, `request_data`, `response_data`, `infinitepay_slug`, `infinitepay_link`, `transaction_nsu`, `order_nsu`, `endpoint`, `http_code`, `ip_usuario`, `user_agent`, `cadastrado_em`) VALUES
(1, 1, 'verificar_pagamento', 'SUCESSO', NULL, NULL, '{\"slug\": \"xEXsKjdqL\", \"handle\": \"rosivalmorais\", \"order_nsu\": \"doacao-1\", \"transaction_nsu\": \"86dce99a-3bf0-48c1-8d29-887aae961bde\"}', '{\"paid\": true, \"amount\": 100, \"success\": true, \"paid_amount\": 100, \"installments\": 1, \"capture_method\": \"pix\"}', NULL, NULL, '86dce99a-3bf0-48c1-8d29-887aae961bde', 'doacao-1', 'https://api.infinitepay.io/invoices/public/checkout/payment_check', 200, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 09:41:35'),
(2, 2, 'verificar_pagamento', 'SUCESSO', NULL, NULL, '{\"slug\": \"7dt5Mu2GxL\", \"handle\": \"rosivalmorais\", \"order_nsu\": \"doacao-2\", \"transaction_nsu\": \"963dbc92-33ed-4cf8-9620-f56d0ebe6caa\"}', '{\"paid\": true, \"amount\": 100, \"success\": true, \"paid_amount\": 100, \"installments\": 1, \"capture_method\": \"pix\"}', NULL, NULL, '963dbc92-33ed-4cf8-9620-f56d0ebe6caa', 'doacao-2', 'https://api.infinitepay.io/invoices/public/checkout/payment_check', 200, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 10:22:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `posts`
--

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `categoria_id` int NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `texto` longtext,
  `capa` varchar(255) DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `cadastrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `posts`
--

INSERT INTO `posts` (`id`, `usuario_id`, `categoria_id`, `titulo`, `slug`, `texto`, `capa`, `status`, `cadastrado_em`) VALUES
(17, 1, 1, 'Acompanhamento social (Distribuição de Cestas Básicas)', 'acompanhamento-social-distribuicao-de-cestas-basicas', '<p style=\"text-align:center\">Nossa missão é ir além da distribuição de cestas básicas. Através do nosso <strong>acompanhamento social</strong>, buscamos entender as necessidades de cada família para oferecer um <strong>apoio completo e contínuo</strong>. Acreditamos que, com <strong>atenção e solidariedade</strong>, é possível construir um futuro mais digno e com mais esperança para todos.</p><p> </p>', 'projeto-cesta-do-bem-distribuicao-de-cestas-basicas_1.jpg', 1, '2024-03-04 14:05:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `level` int NOT NULL DEFAULT '1',
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telefone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url_video` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cpf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `texto` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` int NOT NULL DEFAULT '0',
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `cadastrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `level`, `nome`, `email`, `senha`, `telefone`, `url_video`, `cpf`, `texto`, `status`, `token`, `ultimo_login`, `cadastrado_em`, `atualizado_em`) VALUES
(1, 3, 'Rosival', 'contato@devmorais.com.br', '$2b$10$4ndgH/3ZDFyrLuPNmw9Hc.SV6rSb44MFV2tKLsq52TB7pmALOiWY2', '61983411859', NULL, NULL, NULL, 1, NULL, '2026-04-12 10:42:14', '2023-04-02 22:02:35', '2024-03-02 22:39:19'),
(94, 1, 'Maria Ayda Macêdo da Costa e Silva', 'ayda.angel@hotmail.com', '$2y$10$OEj.79D5dhQRMXSLY2559e8Sm1RfV2zfY3ATmEgM.Wh1iUZVau26e', '61994405608', 'https://youtube.com/shorts/VQ9NNTti-U8?feature=share', '28722205187', '<p>A única renda mensal da família, que é composta por uma adulta de 65 anos e duas crianças (uma de 2 anos), é o Bolsa Família. A mulher, que trabalhava como cuidadora de idosos, não conseguiu se aposentar. Ela cuida de uma criança de dois anos de idade e sofre de ansiedade e depressão por causa dos problemas do dia a dia.</p>', 1, NULL, NULL, '2025-08-09 00:44:52', '2025-08-12 14:36:36'),
(95, 1, 'Laurinda Maria Da Conceição ', 'larindadac@gmail.com', '$2y$10$cdesBgoe3VPVm4rgCp2ZpuNuZBrL5Z2vNoFNOcbMgcOSfiuUyUgbW', '992145283', 'https://youtu.be/NsELKEVbxMQ', '94314497149', '<p>A família é composta por mais de sete pessoas e recebe o auxílio do Bolsa Família. O marido, que é aposentado, ficou alguns meses sem receber sua aposentadoria e toma remédios para dor. Todos os membros da família são saudáveis.</p>', 1, NULL, NULL, '2025-08-09 09:27:22', '2025-08-09 23:56:33'),
(103, 1, 'Antônio Martins ', 'teste@gmail.com', '$2y$10$YMzCkUslIC2Jpd2IxHjiCO1wH7tmYTmvfBvIl.tKFfjiTZ0h1FXyO', '619834159', '', '03595350111', '<p>Sao 7 pessoas que moram juntos, tem um acamado que precisa de assistência de ajuda. Aposentado com um salário mínimo. Gauagahabahvshsjsbshwbssbshsb</p>', 1, NULL, NULL, '2025-08-23 09:43:34', NULL),
(118, 1, 'Maysa Araújo da paz ', 'fixo407601@gmail.com', '$2y$10$UV2xe/whQYLOpXqn0/OmNuLIr2E0Ifk6A3AOZDI68v4KsCxSgycl6', '61992862608', NULL, NULL, 'Preciso', 1, NULL, NULL, '2025-09-12 09:59:22', NULL),
(121, 1, 'MARIA MACEDO DA COSTA', 'fixo582585@gmail.com', '$2y$10$ckOKOGWNQc8ucFOEkWc4yOCLwsySjTvVCXTjl7TnijmWK8RcMNM4q', '(61) 99197-3322', NULL, NULL, 'No momento estou sem trabalhar e sem renda ', 1, NULL, NULL, '2025-09-12 23:31:07', NULL),
(122, 1, 'Anatália Cesário Nery ', 'fixo90322@gmail.com', '$2y$10$qRYvVnsEgY5CUORkIyx4/eWH2zCM2f1Nysb2E2csYsvlD0qyexB8q', '8999826946', NULL, NULL, 'Recebo o bolsa família moro com os meus 4 netos ', 1, NULL, NULL, '2025-09-17 19:35:00', NULL),
(123, 1, 'Daniela da Silva aguy', 'fixo991253@gmail.com', '$2y$10$bRhKHpdZL2m8GEscgePPc.z5fOi1jQquFBV9DgdNdjsTiL7jr187W', '(61) 99215-2391', NULL, NULL, 'Minha reda não dá Nei pra paga o aluguel tô precisando muito ', 1, NULL, NULL, '2025-09-18 14:48:23', NULL),
(124, 1, 'DEBORA SILVA AGUIAR', 'fixo869735@gmail.com', '$2y$10$G2NIt5nar2WhtK2o6oVSXug.Hvfl.BbibuAZZh5zHIlexKssyc35K', '61994527848', NULL, NULL, 'Sou de renda baixa \n E qualquer ajuda, já me ajuda! ', 1, NULL, NULL, '2025-09-19 13:24:40', NULL),
(125, 1, 'Nilson Gomes de Freitas ', 'fixo297711@gmail.com', '$2y$10$z9Z8h0AUJnUoZ4vha9qLMOv9Q3HaIKPvFFFmvh8LqUD.qnZMVyds2', '61 994162591 ', NULL, NULL, 'Trabalho automo ,não tenho emprego fixos ,e nem condições de trabalhar devido problemas na coluna ', 1, NULL, NULL, '2025-09-21 11:04:49', NULL),
(126, 1, 'Maria Salete da Silva ', 'fixo738523@gmail.com', '$2y$10$Gqw5UdEtVodjsFt0hd6wA.AjMANyinpfI7K.rGWJmKLE47K99iMRG', '6199232843', NULL, NULL, 'Recebo uma Um auxilio Idoso pra comprar as coisas pra mim  pra  casa ', 1, NULL, NULL, '2025-09-26 13:28:10', NULL),
(127, 1, 'Lucilia Maria da Silva Oliveira ', 'fixo114841@gmail.com', '$2y$10$TxVBwL5/VsjkgupDu.WdGeCB.FnC6NTSmib2YxSMmawh4mfGpOtW.', '61998423243', NULL, NULL, 'Sou aposentado,porém meu salário é só pra comprar remédio gastos hospitalares ', 1, NULL, NULL, '2025-10-01 18:51:08', NULL),
(128, 1, 'Lorrane ferreira da Silva ', 'fixo164366@gmail.com', '$2y$10$xeNMKtcIubVY6dQTMazfde8bSe0Ce7tIw6HUtorwmOhqPT6Rlv9rG', '61994013719', NULL, NULL, 'Não tenho renda, eu não tenho muito o Que falar sobre mim kk', 1, NULL, NULL, '2025-11-29 16:09:14', NULL),
(129, 1, 'Simone Santos da Silva ', 'fixo131850@gmail.com', '$2y$10$K3ElOBWyGsqdSVGD0Dx3G.G.8UGEm6ZdRsQpBiPtiMx5cDv2zGz3S', '61991177906', NULL, NULL, 'Ganho um salário mínimo ', 1, NULL, NULL, '2025-11-30 16:16:55', NULL),
(130, 1, 'Mariana Antônia do Nascimento ', 'fixo648129@gmail.com', '$2y$10$Czz7.XcugToZgySUn5U.6e/ooojPuoBJx1UvkW/XVrPZRRorLlicu', '61998210615', NULL, NULL, 'Eu tô precisando porque  fasso  bico \r\n\r\n', 1, NULL, NULL, '2025-11-30 16:21:34', NULL),
(131, 1, 'Mariana Antônia do Nascimento ', 'fixo234303@gmail.com', '$2y$10$sP.cXLKESLYK2PumpkzV4uGfTX83ajBqKe8MYVLg/TAb1GRxdxv8S', '61998210615', NULL, NULL, 'Ganho um salário mínimo ', 1, NULL, NULL, '2025-11-30 16:28:17', NULL),
(132, 1, 'Pâmela Santos da Silva ', 'fixo996781@gmail.com', '$2y$10$aovyCI1ppoZcggZJMV0E/uEgg7UXNnyECPhftW2uDyAwlhvqVkliG', '61 99453-3842', NULL, NULL, 'Estou desempregada, estou sem renda\r\nTenho 1 filha ', 1, NULL, NULL, '2025-11-30 16:46:35', NULL),
(133, 1, 'Maria Luiza evangelista de Almeida Costa ', 'fixo259331@gmail.com', '$2y$10$Il/ek3OcURrfsUTRLsII2O6aMvkfCcYaCGCugWqDZKi9icyEV41Sm', '61993026007', NULL, NULL, 'Sem renda ', 1, NULL, NULL, '2025-12-02 17:38:06', NULL),
(134, 1, 'Claudiana Cristina Da Silva ', 'fixo835887@gmail.com', '$2y$10$iYvlzYPmjSNw761plWakN.lG.OVoHdQZRSTPTaWbUZeJfMsscD6UO', '991866099', NULL, NULL, 'Só tenho o bolsa família no momento ', 1, NULL, NULL, '2026-01-21 15:17:35', NULL),
(135, 1, 'Wanessa ', 'fixo129334@gmail.com', '$2y$10$PtudR4os7VLcSVYLvdZrwO7frWWVw5VHidi0dVbfAzjhrC7CLv7Kq', '61991574070', NULL, NULL, 'Eu recebo somente o bolsa com dinheiro do meu bolsa pago aluguel água e luz não sobra quase nada e venho passando dificuldade com meus 3 filhos tenho um bebê de 3 mês que toma fórmula o nan de 0 a 6 to sem condições de comprar no momento ', 1, NULL, NULL, '2026-01-21 15:18:43', NULL),
(136, 1, 'Hayra', 'fixo899705@gmail.com', '$2y$10$.pXhcTl5aYfWquMQch1Dz.oEfeJEDVHqk/j3aPcLFUp3.KhjUq5du', '61993755031', NULL, NULL, '00 estou desempregada ', 1, NULL, NULL, '2026-01-21 15:19:03', NULL),
(137, 1, 'Debora Cristina', 'fixo186265@gmail.com', '$2y$10$oQqcBV6cruEN4dSB9hoLt.4FJPbc4FJVc23YFh3rQ/Ha/ci8yEsh.', '61995534395', NULL, NULL, 'moro de aluguel e nao trabalho tenho 3 filhos um de 10 , 4 e 7 meses recebo bolsa fml mas nunca da pra nada...', 1, NULL, NULL, '2026-01-21 15:21:20', NULL),
(138, 1, 'Tayza PAULA de MORAIS Barbosa', 'fixo479502@gmail.com', '$2y$10$0mAtg7Ik6QPjCfKkSlejVeGPBjaIUX6b0mKlDFi.O2zbZxIaNWlrW', '61995697795', NULL, NULL, 'Então no momento não trabalho pois tenho uma filha especial,tem no total tres crianças em casa , dependendo somente do bpc loas .', 1, NULL, NULL, '2026-01-21 15:22:23', NULL),
(139, 1, 'Regiane de Andrade Santos ', 'fixo329226@gmail.com', '$2y$10$LnmJa04xWs/kh87LdYpI6OUFyZrIWe13FpI9oQBAls.KY96lxjY6O', '61998671595', NULL, NULL, '600reais do bolsa família ', 1, NULL, NULL, '2026-01-21 15:25:25', NULL),
(140, 1, 'SILVIA BRITO MOREIRA', 'fixo861832@gmail.com', '$2y$10$FYgbIWWJDvbC.85uKx6vHu9eaAeE7WCLOoSJCdEcyaULNXscwvebO', '61993789978', NULL, NULL, 'Tô desempregada só meu marido que tá trabalhando de mecânico não tem ajuda de governo somos 4 pessoas na casa tem um bebê ', 1, NULL, NULL, '2026-01-21 15:26:23', NULL),
(141, 1, 'GORETE MARTINS CAVALCANTE Da Cunha', 'fixo329288@gmail.com', '$2y$10$xAyfw936Pcg1ZtrJEXQJQ.9uT5XoO9nh1S/CqOz389MGgk8D3OKuS', '61991616673', NULL, NULL, 'Desempregada', 1, NULL, NULL, '2026-01-21 15:31:28', NULL),
(142, 1, 'Liliane da Silva Almeida ', 'fixo104094@gmail.com', '$2y$10$/RbolOLyCVlyEe2uX4NcSuUg6FqD0NVmar3RwES2j..Y27i.YZYka', '61993589588', NULL, NULL, 'Boa tarde meu nome é Liliane tenho 32 anos só recebo bolsa família recebo 950 para o meu aluguel de 450 tá muito difícil para mim não tenho auxiliar das aluguel mais uma cesta desce sobe muito para os meus filhos sua mãe de quatro filhos', 1, NULL, NULL, '2026-01-21 15:33:07', NULL),
(143, 1, 'Valquiria Lourenço pires ', 'fixo218145@gmail.com', '$2y$10$DH7j3X.1pVmTjVO3A/KEaeCtVSJEZB0Bs6OH8LthuXZ1KqwINdYYG', '61991883710', NULL, NULL, 'Sou mae solo de 6 criancas mim encontro com meu bolsa família  blokeado e sem condicoes de dar o basico aos meus filhos ', 1, NULL, NULL, '2026-01-21 15:35:19', NULL),
(144, 1, 'Isabel de Sousa Ribeiro Alves ', 'fixo856956@gmail.com', '$2y$10$IcthCfMzbrHt03rfzKl1OOvjQXq.Vcv3zod3v8TwN5So3wvX7zujG', '61995013847', NULL, NULL, 'Não tenho renda ', 1, NULL, NULL, '2026-01-21 15:36:35', NULL),
(145, 1, 'Rosária da Silva', 'fixo377793@gmail.com', '$2y$10$BQHmnJxMnpiwSeO9JJDd/e6M0a2tFhTPnseXcj3X2K28ZtQ.p.i06', '61993376297', NULL, NULL, 'Sou Rosária tenho 50 anos a minha renda é r$ 600 sou mãe de 6 filhos com esse dinheiro eu compro meus remédios mas nem toda vez dá\r\n', 1, NULL, NULL, '2026-01-21 15:38:30', NULL),
(146, 1, 'Laise dosSantos lima ', 'fixo312724@gmail.com', '$2y$10$RvWeH5lZqoKd1UPRJ06KEu3ueahl/Loy/IDQS54q.QvbPJq0VOY2G', '(61) 98115-6552', NULL, NULL, 'Chamo laise tenho duas filhas uma de 6 outra de 9 meses estou desempregada no momento.', 1, NULL, NULL, '2026-01-21 15:39:22', NULL),
(147, 1, 'Nelcy Pereira Rodrigues Santana ', 'fixo344804@gmail.com', '$2y$10$S93d9.y7BqPzk4mgVxDisOX.fOnS/whVvgXs.JZNbMmHxhi8zIOV6', '6199446-6227 ', NULL, NULL, 'Sou casada e moro de aluguel tenho filho acamado e meu esposo tem diabetes neste momento to precisando de uma ajuda acabamos de chegar da Bahia ', 1, NULL, NULL, '2026-01-21 15:41:43', NULL),
(148, 1, 'Shayane Barbosa Da silva ', 'fixo486386@gmail.com', '$2y$10$ylC8hqbcQEtDAP.ch/em0u3gaOWIN4HBkftV680n4S1vkpU8wtjye', '61991829120', NULL, NULL, 'Minha renda e normal ', 1, NULL, NULL, '2026-01-21 15:42:53', NULL),
(149, 1, 'Rejane Barbosa de castro ', 'fixo347740@gmail.com', '$2y$10$RjP2Q6uYjjfzcO/M9bYJo.gqw6Afz.lY.3/gl74nkITTvp94fapom', '61992904689', NULL, NULL, 'Não trabalho tenho somente o bolsa família, tenho 5 filhos um é bebê só tem 6 meses, e estou precisando muito de ajuda.', 1, NULL, NULL, '2026-01-21 15:55:57', NULL),
(150, 1, 'Lucilene Santos oliveira ', 'fixo598771@gmail.com', '$2y$10$WfC5qWL7woAno9QVv0dcYe5KI2onj0Y0Bhq7kKrMzKrlx/aY/6O9W', '61996096389', NULL, NULL, 'Tenho filhas ,sou sozinha ', 1, NULL, NULL, '2026-01-21 16:00:06', NULL),
(151, 1, 'Lucilene Santos oliveira ', 'fixo910767@gmail.com', '$2y$10$dGgbF6MCGiVFxDwYoq0MIeZjLcC02BTPzG5mbg2fxMNisc0rT10Be', '61996096389', NULL, NULL, 'Tenho filhas ,sou sozinha ', 1, NULL, NULL, '2026-01-21 16:00:53', NULL),
(152, 1, 'Mariana Marques Magalhães', 'fixo881927@gmail.com', '$2y$10$WWNp6ZtesocpbMJnA5Bs0uwE06QgkDElSVZl66VFikGbWKHV1gurG', '61992089138', NULL, NULL, 'Tenho 19 anos moro com minha mãe 3 irmãos e uma filha de 2 meses só minha mãe trabalha e eu não tenho renda fixa e também não posso trabalhar por agora pq minha neném e novinha', 1, NULL, NULL, '2026-01-21 16:02:06', NULL),
(153, 1, 'MARIA DAS GRACAS RICARTE DA SILVA', 'fixo987971@gmail.com', '$2y$10$2GXdkiVW1k74FRygmtRsTu5mD2/n0lTyAHBP2II2e8NjdZ/.rUVSa', '61994520385', NULL, NULL, 'Sou mae de tres meninas , minha renda e so bolsa familia  faco uma faxima por mes de 150 reais  ', 1, NULL, NULL, '2026-01-21 16:02:12', NULL),
(154, 1, 'Ana clara pereira da Silva ', 'fixo322491@gmail.com', '$2y$10$p0hqn2k4Icz3cG8tw/ObP.bqKscWrXwqHDoxZKGgg8jzJezbOhpTa', '61995139150', NULL, NULL, 'Eu não recebo nem um auxílio não recebo bolsa família ', 1, NULL, NULL, '2026-01-21 16:03:11', NULL),
(155, 1, 'GRAZIELA', 'fixo205089@gmail.com', '$2y$10$fkYSg9jEnOsVIr0KP1ckeupgKcKzbr6CQsjxJ5URA693sT0n.ytnG', '61992111522', NULL, NULL, 'N tenho renda n trabalho quem trabalha só eh meu esposo ', 1, NULL, NULL, '2026-01-21 16:04:33', NULL),
(156, 1, 'Maria do Carmo Souza ', 'fixo707616@gmail.com', '$2y$10$wH.aweErk5hr1/Eu67JC8ORGZhsdpt7w1O/9ZkbKs2SvEJ1keAAla', '6199584 7989', NULL, NULL, 'Bolsa família ', 1, NULL, NULL, '2026-01-21 16:04:40', NULL),
(157, 1, 'Maria Francisca Dos Santos Silva ', 'fixo465003@gmail.com', '$2y$10$8sH673a6iuol.S2aovjmZOImL.AIg8nMWLge8oPkm0HvoLnJUXpr.', '61994357611', NULL, NULL, 'Não Trabalho', 1, NULL, NULL, '2026-01-21 16:04:58', NULL),
(158, 1, 'Maria do Carmo Souza ', 'fixo429045@gmail.com', '$2y$10$rZQUykmBUVJhbDTp2aiU6uQ6T3dJklHevp0YwOWYcgNvqc0oeAycC', '6199584 7989', NULL, NULL, '850', 1, NULL, NULL, '2026-01-21 16:06:01', NULL),
(159, 1, 'Elisdete Rodrigues munduruca ', 'fixo150578@gmail.com', '$2y$10$MCJrFfqV0KQ15UeeNCqwlOo4k6.sceb1W9OfE7snb6wCqw7MHcBzO', '62998288854', NULL, NULL, 'Minha renda 500', 1, NULL, NULL, '2026-01-21 16:19:12', NULL),
(160, 1, 'Elisdete Rodrigues munduruca ', 'fixo219609@gmail.com', '$2y$10$W3QFbRdSoJlGriC63CqPAuFZ/HmpGNzQRH4HWr5DaFLQ9oJUz32a.', '62998288854', NULL, NULL, 'Não tenho renda', 1, NULL, NULL, '2026-01-21 16:24:18', NULL),
(161, 1, 'Tarsila Onuma de Miranda Costa ', 'fixo338899@gmail.com', '$2y$10$EZ5NepP8M1YWz0DxjQPlo.4D0GRo3csmqqh3DNMuCfhzrPSNxV2SW', '991067459', NULL, NULL, 'Sou surda, sou mãe. Só recebo bolsa de família.\r\n\r\n\r\n', 1, NULL, NULL, '2026-01-21 16:25:22', NULL),
(162, 1, 'Janice de Sousa Silva ', 'fixo301958@gmail.com', '$2y$10$F.HuJ19PB0QoT9iVQcM4E.cVH8aSSwRCA7jGqU5gttrAQx5QqxqRS', '61992696833', NULL, NULL, 'Só bpc', 1, NULL, NULL, '2026-01-21 16:25:29', NULL),
(163, 1, 'Francisca das chagas ', 'fixo910704@gmail.com', '$2y$10$pKtt7Y9KAU8TITQrsWq13uTq0sm.NmW6RFcP11/r6PgKikU0HWu8e', '61983574734', NULL, NULL, 'N tenho renda', 1, NULL, NULL, '2026-01-21 16:29:09', NULL),
(164, 1, 'Railsa de Oliveira Marçal ', 'fixo953036@gmail.com', '$2y$10$v/KDwByOZxZqO19guKuWCecg/0eBtKEi3W066k9aFTmc1dEzPRPv.', '61993147197', NULL, NULL, 'A única renda que tenho é bolsa familiar 650 ,sou mãe e vó moro com meu filho de 14 anos e minha notinha de 2 anos , estou no momento enfrentando um batalha contra o câncer de mama onde até agora meu tratamento só pode realiza um consulta com mastologista , estou aguardando na regularização para eu pode min interna,  preciso muito da cesta pra pode deixa alimento para meu filho.  Deus abençoe ', 1, NULL, NULL, '2026-01-21 16:32:01', NULL),
(165, 1, 'Lucimar ', 'fixo396602@gmail.com', '$2y$10$6iRNC8.C.vPnXRVPZ9HSzuiH2svML5w70og9KXPWrGgzGrAz3JDjy', '61995955605', NULL, NULL, '459;00reais sou mãe solteira tenho dois filhos moro  de aluguel e no momento  não consigo trabalhar ', 1, NULL, NULL, '2026-01-21 16:50:16', NULL),
(166, 1, 'Larissa Do Carmo Ribeiro ', 'fixo951069@gmail.com', '$2y$10$mqG.MR8sdcstjI0poy1qW.Q5wEGVh3ZuEbd5iah8/VLBdV/pB.wYO', '61993990768', NULL, NULL, 'Tenho uma renda do bolsa família 850$, tenho 3 filhos pequenos e estou gestante de 7 mês,ainda não tenho nada pro meu bebê,e precisava muito de uma cesta básica,pós eu e meus filhos estamos em uma situação muito difícil, peço ajuda ', 1, NULL, NULL, '2026-01-21 17:02:18', NULL),
(167, 1, 'GERCILENE JOSE DA ROCHA', 'fixo964691@gmail.com', '$2y$10$NFSC.f5EeAWo5X1JXA5e9.UGBWSR3L24j5NwfYMp00JsHfm4hlvvS', '61995812416', NULL, NULL, 'Minha reda e 1400 tenho 5 filhos e sou mãe solo', 1, NULL, NULL, '2026-01-21 17:03:37', NULL),
(168, 1, 'Dalila Maria Marçal dos santos ', 'fixo95130@gmail.com', '$2y$10$dd4YqUZsq6isKPvridU.d.YjOQWqWuKYDuBbyWf/8J4GXplNDG40q', '61996751997', NULL, NULL, 'Eu trabalho vendedor doces mais por agora eu não tou podendo trabalhar \r\nEu tive bebê agora mais eu tenho que tá de resguardo', 1, NULL, NULL, '2026-01-21 17:10:59', NULL),
(169, 1, 'Jéssica Lorrane pereira Duarte', 'fixo875426@gmail.com', '$2y$10$o.NDeyk1rqcX8b7c26v5k.Ix.TMV41mwQ6sESo31oVjyP8xKWy.x2', '61993527250', NULL, NULL, 'Estou desempregada z tento sobreviver apenas com o bolsa família porém eu pago aluguel e tenho um filho de 1 ano e não tenho renda para compra alimentação e derivados como produtos de higiene pessoal também ', 1, NULL, NULL, '2026-01-21 17:13:08', NULL),
(170, 1, 'Fernanda camargos vilar ', 'fixo381778@gmail.com', '$2y$10$7u.jYoq5nxTFe/rFeI8x4uj/KPrL1l2UI124tPHwdWzTE3Pco5JZS', '61991474288', NULL, NULL, 'Só recebo bolsa família ', 1, NULL, NULL, '2026-01-21 17:15:58', NULL),
(171, 1, 'Silvana Dos Santos ', 'fixo806846@gmail.com', '$2y$10$6DarkxR81c7.xD4b8WjTFOcGUc2kSgT/wAWTT6q9stNuOd694A3N6', '61993724916', NULL, NULL, 'Bem pouca mora eu meu esposo e meu filho viciado em bebida alcoólica ', 1, NULL, NULL, '2026-01-21 17:22:00', NULL),
(172, 1, 'Thaiane Silva Ferreira ', 'fixo449506@gmail.com', '$2y$10$FCzYnrix7tnIZTSVBJXmjOq/5zNKk4XKnxPSqm3jbVvePXkHvOYO.', '61994269649', NULL, NULL, 'Sou mãe solteira tenho 3 filhos \r\n1 tem epilepsia moro na casa da minha mãe . Tenho 1 deficiência na perna que me impossibilita trabalhar minha única renda e o bolsa família ', 1, NULL, NULL, '2026-01-21 17:30:20', NULL),
(173, 1, 'MARCIA BEZERRA SILVA', 'fixo721484@gmail.com', '$2y$10$MG2xYk1xnfOB.Z4nNqoFxOLSofEEOeBdhcBuHZzWOOfZL0iRPCtAq', '61993011245', NULL, NULL, 'Só recebo bolsa família no momento ', 1, NULL, NULL, '2026-01-21 17:31:12', NULL),
(174, 1, 'Geovanna gomes maia', 'fixo51294@gmail.com', '$2y$10$456v/RyF5EOTPPOZJ1DQi.az8TjHsMnElu2eE5kyXas3qOAihBRuW', '61993249377', NULL, NULL, 'Recebo pouco de vez enquando faço bico de faxina tenho 4 filhos o pai morreu ', 1, NULL, NULL, '2026-01-21 17:49:00', NULL),
(175, 1, 'ROSENI DE FREITAS BRAZ', 'fixo326449@gmail.com', '$2y$10$XhqAKAY5doEETNNgKYqoXeKIXP77AIiaeA4S6W7Q0lVN1nxNQuQ9S', '61995029087', NULL, NULL, '600 reais  \r\nSua camada compra o remédio pago luz e água sua mulher e o homem da casa frio um acidente o carro capotou estou em cima de uma cama você v\r\nai me ajudar que Deus abençoe muito obrigada tenha um bom dia e um boa tarde e tenha uma boa noite\r\n.', 1, NULL, NULL, '2026-01-21 17:49:05', NULL),
(176, 1, 'ROSENI DE FREITAS BRAZ', 'fixo173113@gmail.com', '$2y$10$wOW6q..6pQcL9FVZfGj/F.JJg1ACaQiQUr6G9elnFLmjlspu1GX3K', '61995029087', NULL, NULL, '600 reais \r\nCompra o remédio seu acidente sou camada preciso muito de uma ajuda sua mãe e o pai pagou Luiz pagou água puder me ajudar obrigado tenha um bom dia tenha uma boa noite tenha uma boa tarde', 1, NULL, NULL, '2026-01-21 17:53:22', NULL),
(177, 1, 'Suianne Gomes de Lima ', 'fixo252910@gmail.com', '$2y$10$sdKFKLHgKlYRVKbXE4rkIeNf0/AMMWBmFlq4iYnLzqdA5Ro4D6gz2', '61993267111', NULL, NULL, 'Minha renda é  105 ganho pouco.\r\nSobre mim eu sou mãe solo de três filhos. Crio meus filhos com muita dificuldade mais sempre coloco Deus a frente de tudo. Sou grata por tudo..... tudo que vem a mim só desejo em dobro pra todos que me ajudam.', 1, NULL, NULL, '2026-01-21 17:58:23', NULL),
(178, 1, 'Andrielle Soares Pimenta ', 'fixo301421@gmail.com', '$2y$10$qcEGgOgPjK7z0pzttjyoXO0iOifKtPz0KzDfYu4PpRVWjurWNGD5S', '61996985882', NULL, NULL, 'Sou mãe solo e estou desempregada passando por necessidade com meus filhos!', 1, NULL, NULL, '2026-01-21 18:08:05', NULL),
(179, 1, 'Maria do Carmo ', 'fixo692843@gmail.com', '$2y$10$rD1FhSAWC4p2ssK2/wslZ.dJ7IpBYUwtMN0UHr8LlrIUW/NiGZtN.', '61 991742371', NULL, NULL, 'No momento não trabalho só meu esposo que faz bico moramos de aluguel tenho 3 filhas e estou grávida de 1 mês ', 1, NULL, NULL, '2026-01-21 18:16:29', NULL),
(180, 1, 'VALDELICE DOS SANTOS SILVA', 'fixo144495@gmail.com', '$2y$10$z8iy.S3LHf42qxyTowlC3uaiw2HSKrkhYjn/Hh2XbUrIltnbs/WBC', '61984201524', NULL, NULL, 'Tenho 6 filhos e um neto e todos mora comigo e estou grávida e Desempregada no momento e estou vivendo com auxílio do bolsa família ', 1, NULL, NULL, '2026-01-21 19:10:20', NULL),
(181, 1, 'Taynnara Pereira da Silva ', 'fixo926765@gmail.com', '$2y$10$pEReK7ItX.yqcwBUq2SphONtSU0W/UY8VVa6hUlMN8nJgCwg1kPMG', '61995247305', NULL, NULL, 'No momento eu estou desempregada   vivo de uma pensão e pego um auxílio  cuido dos meu filho meu marido tão ta desempregado porque sofreu acidente aí não pode trabalhar agora aí nois vive do que eu pego', 1, NULL, NULL, '2026-01-21 19:15:38', NULL),
(182, 1, 'Iohany vaz de souza ', 'fixo245294@gmail.com', '$2y$10$s2OhbYS4M2XPna0YCcHpB.HQgRXNkGbZa9uD6alsoelb2UKWFnPZi', '61993766900', NULL, NULL, 'Sou mãe solo  estou desempregada minha única renda bolsa família. ', 1, NULL, NULL, '2026-01-21 19:33:30', NULL),
(183, 1, 'juscineide oliveira pereira', 'fixo847246@gmail.com', '$2y$10$DF24vlePWQtumm/xAAJIvugoWrPnC1zB5v3Mc16C7sWz2Qsap2uKS', '61991506946', NULL, NULL, '200', 1, NULL, NULL, '2026-01-21 20:40:03', NULL),
(184, 1, 'juscineide oliveira pereira', 'fixo341087@gmail.com', '$2y$10$WQfafL7ZTKZmJRH7Sy1ne.mN8IgEij5VAKChvuP/vUa/9nYg5nOWW', '61991506945', NULL, NULL, 'Sou mãe solteira tenho três filhos moro na invasão', 1, NULL, NULL, '2026-01-21 21:11:47', NULL),
(185, 1, 'juscineide oliveira pereira', 'fixo191129@gmail.com', '$2y$10$9nfP1XPtNFMccnPqn43BBOlR031SQ/S12u.EhdEelYi2EsokG7waS', '61991506945', NULL, NULL, 'Sou mãe solteira tenho três filhos moro na invasão', 1, NULL, NULL, '2026-01-21 21:12:46', NULL),
(186, 1, 'Dalila Gomes da Silva ', 'fixo141929@gmail.com', '$2y$10$T864lzMRInHdOfWuztU2ee9D/k3Vac5yekvhAlw08uzuLveUdTe2K', '61994601988', NULL, NULL, 'Tenha uma renda baixa faço parte do programa cad único, ', 1, NULL, NULL, '2026-01-21 23:57:20', NULL),
(187, 1, 'Fernando Aguiar da Costa Morais', 'fixo648714@gmail.com', '$2y$10$WRKEJXEIlb0Q0MeX8Ra6fO2EzJg/4Gr7B/oT1ebPx5/.TE9Kfcdda', '61983411859', NULL, NULL, 'teset', 1, NULL, NULL, '2026-04-11 14:27:54', NULL),
(188, 1, 'Rita Oliveira mendes', 'fixo952605@gmail.com', '$2y$10$9ZaeBWFcpjBZIsPaFwnNMem9x6laoFTOGNIBTJJ47s.hjKQVew3bC', '61995182595', NULL, NULL, 'Cadastro único ', 1, NULL, NULL, '2026-03-03 19:17:24', NULL),
(189, 1, 'Valdelice dia Santos Silva', 'fixo217268@gmail.com', '$2y$10$JZX9zs8SM1SVy/Je1DbkPu/EUPeBdt/0g84TkZIE9xPlY4dx7aHV2', '61984201524', NULL, NULL, 'São 8 pessoas que que moram e no momento está grávida ', 1, NULL, NULL, '2026-04-11 18:50:02', NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `doacoes`
--
ALTER TABLE `doacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_doacao_usuario` (`usuario_id`);

--
-- Índices de tabela `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_endereco_usuario` (`usuario_id`);

--
-- Índices de tabela `imagens`
--
ALTER TABLE `imagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `logs_pagamento_infinitepay`
--
ALTER TABLE `logs_pagamento_infinitepay`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_doacao` (`doacao_id`);

--
-- Índices de tabela `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_post_categoria` (`categoria_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `doacoes`
--
ALTER TABLE `doacoes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de tabela `imagens`
--
ALTER TABLE `imagens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `logs_pagamento_infinitepay`
--
ALTER TABLE `logs_pagamento_infinitepay`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `doacoes`
--
ALTER TABLE `doacoes`
  ADD CONSTRAINT `fk_doacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `enderecos`
--
ALTER TABLE `enderecos`
  ADD CONSTRAINT `fk_endereco_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs_pagamento_infinitepay`
--
ALTER TABLE `logs_pagamento_infinitepay`
  ADD CONSTRAINT `fk_log_doacao` FOREIGN KEY (`doacao_id`) REFERENCES `doacoes` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_post_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

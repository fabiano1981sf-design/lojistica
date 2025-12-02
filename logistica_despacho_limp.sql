-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 18-Nov-2025 às 11:26
-- Versão do servidor: 5.7.24
-- versão do PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `logistica_despacho`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `aparelhos`
--

CREATE TABLE `aparelhos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `serial` varchar(100) NOT NULL,
  `status` enum('Disponível','Em Uso','Manutenção') DEFAULT 'Disponível',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `despachos`
--

CREATE TABLE `despachos` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) DEFAULT NULL,
  `data_envio` date DEFAULT NULL,
  `num_sedex` varchar(200) DEFAULT NULL,
  `codigo_rastreio` varchar(20) NOT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_prevista_entrega` date DEFAULT NULL,
  `status` enum('Em Processamento','Em Trânsito','Aguardando Retirada','Entregue','Cancelado') DEFAULT 'Em Processamento',
  `despachante_id` int(11) DEFAULT NULL,
  `origem_nome` varchar(100) DEFAULT NULL,
  `origem_endereco` text,
  `origem_cep` varchar(10) DEFAULT NULL,
  `origem_telefone` varchar(20) DEFAULT NULL,
  `destino_nome` varchar(100) DEFAULT NULL,
  `destino_endereco` text,
  `destino_cep` varchar(10) DEFAULT NULL,
  `destino_telefone` varchar(20) DEFAULT NULL,
  `origem_empresa` varchar(150) DEFAULT NULL,
  `destino_empresa` varchar(150) DEFAULT NULL,
  `nota_fiscal` varchar(50) DEFAULT NULL,
  `transportadora_id` int(11) DEFAULT NULL,
  `transportadora` varchar(100) DEFAULT NULL,
  `num_nota` varchar(200) DEFAULT NULL,
  `anotacao1` text,
  `anotacao2` text,
  `anotacao` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `despachos`
--

INSERT INTO `despachos` (`id`, `nome`, `data_envio`, `num_sedex`, `codigo_rastreio`, `data_criacao`, `data_prevista_entrega`, `status`, `despachante_id`, `origem_nome`, `origem_endereco`, `origem_cep`, `origem_telefone`, `destino_nome`, `destino_endereco`, `destino_cep`, `destino_telefone`, `origem_empresa`, `destino_empresa`, `nota_fiscal`, `transportadora_id`, `transportadora`, `num_nota`, `anotacao1`, `anotacao2`, `anotacao`) VALUES
(2, 'ASSIST TECNICA', '2023-05-22', 'OV42344536BR', 'OV42344536BR', '2023-05-22 00:00:00', NULL, 'Em Processamento', 1, NULL, NULL, NULL, NULL, 'ASSIST TECNICA', NULL, NULL, NULL, NULL, NULL, '143186', 6, 'SEDEX', '143186', 'SEDEX REVERSO', '', NULL),

-- --------------------------------------------------------

--
-- Estrutura da tabela `despacho_mercadoria`
--

CREATE TABLE `despacho_mercadoria` (
  `despacho_id` int(11) NOT NULL,
  `mercadoria_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `mercadorias`
--

CREATE TABLE `mercadorias` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text,
  `peso` decimal(8,3) DEFAULT NULL,
  `volume` decimal(8,3) DEFAULT NULL,
  `valor_declarado` decimal(10,2) DEFAULT NULL,
  `status` enum('Em Estoque','Despachado','Em Trânsito','Entregue','Cancelado') DEFAULT 'Em Estoque',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfil_permissao`
--

CREATE TABLE `perfil_permissao` (
  `perfil_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `perfil_permissao`
--

INSERT INTO `perfil_permissao` (`perfil_id`, `permissao_id`) VALUES
(1, 1),
(1, 2),
(2, 2),
(1, 3),
(3, 3),
(1, 4),
(1, 5),
(2, 5),
(1, 6),
(2, 6),
(1, 7),
(2, 7),
(1, 8),
(2, 8),
(1, 15);

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfis`
--

CREATE TABLE `perfis` (
  `id` int(11) NOT NULL,
  `nome_perfil` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `perfis`
--

INSERT INTO `perfis` (`id`, `nome_perfil`) VALUES
(1, 'Administrador'),
(2, 'Despachante'),
(3, 'Visualizador');

-- --------------------------------------------------------

--
-- Estrutura da tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL,
  `nome_permissao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `permissoes`
--

INSERT INTO `permissoes` (`id`, `nome_permissao`) VALUES
(6, 'atualizar_rastreio'),
(5, 'criar_despacho'),
(8, 'escanear_qrcode'),
(7, 'gerar_qrcode'),
(15, 'gerenciar_configuracoes'),
(2, 'gerenciar_despachos'),
(1, 'gerenciar_usuarios'),
(3, 'rastrear_publico'),
(4, 'visualizar_relatorios');

-- --------------------------------------------------------

--
-- Estrutura da tabela `rastreio_historico`
--

CREATE TABLE `rastreio_historico` (
  `id` int(11) NOT NULL,
  `despacho_id` int(11) DEFAULT NULL,
  `localizacao` varchar(100) DEFAULT NULL,
  `evento` text NOT NULL,
  `data_hora` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `rastreio_historico`
--

INSERT INTO `rastreio_historico` (`id`, `despacho_id`, `localizacao`, `evento`, `data_hora`) VALUES
(6783, 2, 'Migração 100% Completa', 'Migrado do sistema antigo – 17/11/2025', '2025-11-17 10:13:40'),

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_controle`
--

CREATE TABLE `tb_controle` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `data_envio` date NOT NULL,
  `num_sedex` varchar(200) NOT NULL,
  `transportadora` varchar(200) NOT NULL,
  `num_nota` varchar(200) NOT NULL,
  `anotacao1` text NOT NULL,
  `anotacao2` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tb_controle`
--

INSERT INTO `tb_controle` (`id`, `nome`, `data_envio`, `num_sedex`, `transportadora`, `num_nota`, `anotacao1`, `anotacao2`) VALUES
(2, 'ASSIST TECNICA', '2023-05-22', 'OV42344536BR', 'SEDEX', '143186', 'SEDEX REVERSO', ''),


-- --------------------------------------------------------

--
-- Estrutura da tabela `transportadoras`
--

CREATE TABLE `transportadoras` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `transportadoras`
--

INSERT INTO `transportadoras` (`id`, `nome`, `data_cadastro`) VALUES
(1, 'Correios', '2025-11-14 16:59:18'),
(2, 'Jadlog', '2025-11-14 16:59:18'),
(3, 'Latam Cargo', '2025-11-14 16:59:18'),
(4, 'Azul Cargo', '2025-11-14 16:59:18'),
(5, 'TNT', '2025-11-14 16:59:18'),
(6, 'SEDEX', '2025-11-14 17:43:47'),
(7, 'EXPRESSO SAO MIGUEL', '2025-11-14 17:43:47'),
(8, 'BRASPRESS', '2025-11-14 17:43:47'),
(9, 'TRANSPORTES DUMAR', '2025-11-14 17:43:47'),
(10, 'EDEN RENOSTO', '2025-11-14 17:43:47'),
(11, 'KLD', '2025-11-14 17:43:47'),
(12, 'TNT MERCURIO', '2025-11-14 17:43:47'),
(13, 'GOL LINHAS AEREAS', '2025-11-14 17:43:47'),
(14, 'RODONAVES', '2025-11-14 17:43:47'),
(15, 'AZUL LINHAS AEREAS', '2025-11-14 17:43:47'),
(16, 'PRONTO CARGO', '2025-11-14 17:43:47'),
(17, 'PROPRIO', '2025-11-14 17:43:47'),
(18, 'BRAEX', '2025-11-14 17:43:47'),
(19, 'DUMAR', '2025-11-14 17:43:47'),
(20, 'JAMEF', '2025-11-14 17:43:47'),
(21, 'ATIVA', '2025-11-14 17:43:47'),
(22, 'EXPRESSO FIEL', '2025-11-14 17:43:47'),
(23, 'TRANSLOVATO', '2025-11-14 17:43:47'),
(24, 'TRANSPEROLA', '2025-11-14 17:43:47'),
(25, 'ALFA', '2025-11-14 17:43:47'),
(26, 'PAJUÇARA', '2025-11-14 17:43:47'),
(27, 'PAC REVERSO', '2025-11-14 17:43:47'),
(28, 'MAZZINI & RUIZ', '2025-11-14 17:43:47'),
(29, 'RICARDO DE C. REGUERO', '2025-11-14 17:43:47'),
(30, 'MARCIO MIRANDA CRUZ', '2025-11-14 17:43:47'),
(31, 'DHL', '2025-11-14 17:43:47'),
(32, 'VIAÇÃO GARCIA', '2025-11-14 17:43:47'),
(33, 'SEDEX REVERSO', '2025-11-14 17:43:47'),
(34, 'MODULAR TRANSPORTES', '2025-11-14 17:43:47'),
(35, 'AGEX TRANSPORTES URGENTES', '2025-11-14 17:43:47'),
(36, 'TRANSCORSINI', '2025-11-14 17:43:47'),
(37, 'ALLIEXLOG', '2025-11-14 17:43:47'),
(38, 'REUNIDAS', '2025-11-14 17:43:47'),
(39, 'TRANSPORTADORA SABIA', '2025-11-14 17:43:47'),
(40, 'FERMAC', '2025-11-14 17:43:47'),
(41, 'SABIA DE MARILIA', '2025-11-14 17:43:47'),
(42, 'TW TRANSPORTES', '2025-11-14 17:43:47'),
(43, 'PAC', '2025-11-14 17:43:47'),
(44, 'IDEALIZA', '2025-11-14 17:43:47'),
(45, 'TODOBRASIL', '2025-11-14 17:43:47'),
(46, 'BRASPRES', '2025-11-14 17:43:47'),
(47, 'MARCIO MIRANDA', '2025-11-14 17:43:47'),
(48, 'RC FISIO', '2025-11-14 17:43:47'),
(49, 'FERMAC CARGO', '2025-11-14 17:43:47'),
(50, 'TERMACO', '2025-11-14 17:43:47'),
(51, 'VOO TRANSPORTES', '2025-11-14 17:43:47'),
(52, 'MURILO PIFFER', '2025-11-14 17:43:47'),
(53, 'HYGION', '2025-11-14 17:43:47'),
(54, 'JAD LOG', '2025-11-14 17:43:47'),
(55, 'TRANSP SABIA', '2025-11-14 17:43:47'),
(56, 'TRANSP FIEL', '2025-11-14 17:43:47'),
(57, 'EXP SAO MIGUEL', '2025-11-14 17:43:47'),
(58, 'MARCIO M. DA CRUZ', '2025-11-14 17:43:47'),
(59, 'FL BRASIL', '2025-11-14 17:43:47'),
(60, 'MINUANO', '2025-11-14 17:43:47'),
(61, 'PAC - REVERSO', '2025-11-14 17:43:47'),
(62, 'JP DISTRIBUIDORA', '2025-11-14 17:43:47'),
(63, 'ANDORINHA TRANSP.', '2025-11-14 17:43:47'),
(64, 'DHL EXPRESS', '2025-11-14 17:43:47'),
(65, 'RODOAEREO', '2025-11-14 17:43:47'),
(67, 'RODONAVES AEREO', '2025-11-14 17:43:47'),
(68, 'AEROPRESS', '2025-11-14 17:43:47'),
(69, 'AB498865591BR', '2025-11-17 10:00:02');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `senha_hash`, `status`, `created_at`) VALUES
(1, 'admin', 'admin@empresa.com', '$2y$10$8YAhwmtfLc9RdJdmVyRnO.uXORy7vrcSjxNEI.zU6qplyIkHDBnaq', 'ativo', '2025-11-18 08:24:22');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario_perfil`
--

CREATE TABLE `usuario_perfil` (
  `user_id` int(11) NOT NULL,
  `perfil_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `usuario_perfil`
--

INSERT INTO `usuario_perfil` (`user_id`, `perfil_id`) VALUES
(1, 1),
(3, 1),
(2, 3);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `aparelhos`
--
ALTER TABLE `aparelhos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial` (`serial`);

--
-- Índices para tabela `despachos`
--
ALTER TABLE `despachos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_rastreio` (`codigo_rastreio`),
  ADD KEY `despachante_id` (`despachante_id`),
  ADD KEY `transportadora_id` (`transportadora_id`);

--
-- Índices para tabela `despacho_mercadoria`
--
ALTER TABLE `despacho_mercadoria`
  ADD PRIMARY KEY (`despacho_id`,`mercadoria_id`),
  ADD KEY `mercadoria_id` (`mercadoria_id`);

--
-- Índices para tabela `mercadorias`
--
ALTER TABLE `mercadorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Índices para tabela `perfil_permissao`
--
ALTER TABLE `perfil_permissao`
  ADD PRIMARY KEY (`perfil_id`,`permissao_id`),
  ADD KEY `permissao_id` (`permissao_id`);

--
-- Índices para tabela `perfis`
--
ALTER TABLE `perfis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_perfil` (`nome_perfil`);

--
-- Índices para tabela `permissoes`
--
ALTER TABLE `permissoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_permissao` (`nome_permissao`);

--
-- Índices para tabela `rastreio_historico`
--
ALTER TABLE `rastreio_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `despacho_id` (`despacho_id`);

--
-- Índices para tabela `tb_controle`
--
ALTER TABLE `tb_controle`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `transportadoras`
--
ALTER TABLE `transportadoras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `usuario_perfil`
--
ALTER TABLE `usuario_perfil`
  ADD PRIMARY KEY (`user_id`,`perfil_id`),
  ADD KEY `perfil_id` (`perfil_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aparelhos`
--
ALTER TABLE `aparelhos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `despachos`
--
ALTER TABLE `despachos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7420;

--
-- AUTO_INCREMENT de tabela `mercadorias`
--
ALTER TABLE `mercadorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `perfis`
--
ALTER TABLE `perfis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `permissoes`
--
ALTER TABLE `permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `rastreio_historico`
--
ALTER TABLE `rastreio_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9465;

--
-- AUTO_INCREMENT de tabela `tb_controle`
--
ALTER TABLE `tb_controle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7421;

--
-- AUTO_INCREMENT de tabela `transportadoras`
--
ALTER TABLE `transportadoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `despachos`
--
ALTER TABLE `despachos`
  ADD CONSTRAINT `despachos_ibfk_1` FOREIGN KEY (`despachante_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `despachos_ibfk_2` FOREIGN KEY (`transportadora_id`) REFERENCES `transportadoras` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `despacho_mercadoria`
--
ALTER TABLE `despacho_mercadoria`
  ADD CONSTRAINT `despacho_mercadoria_ibfk_1` FOREIGN KEY (`despacho_id`) REFERENCES `despachos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `despacho_mercadoria_ibfk_2` FOREIGN KEY (`mercadoria_id`) REFERENCES `mercadorias` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `perfil_permissao`
--
ALTER TABLE `perfil_permissao`
  ADD CONSTRAINT `perfil_permissao_ibfk_1` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `perfil_permissao_ibfk_2` FOREIGN KEY (`permissao_id`) REFERENCES `permissoes` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `rastreio_historico`
--
ALTER TABLE `rastreio_historico`
  ADD CONSTRAINT `rastreio_historico_ibfk_1` FOREIGN KEY (`despacho_id`) REFERENCES `despachos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `usuario_perfil`
--
ALTER TABLE `usuario_perfil`
  ADD CONSTRAINT `usuario_perfil_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_perfil_ibfk_2` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

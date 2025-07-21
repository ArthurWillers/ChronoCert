-- phpMyAdmin SQL Dump
-- version 5.2.2-1.fc42
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 21, 2025 at 01:02 PM
-- Server version: 10.11.11-MariaDB
-- PHP Version: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chronocert`
--

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `fk_curso_id` int(11) NOT NULL,
  `carga_maxima` int(11) NOT NULL DEFAULT 40
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`id`, `nome`, `fk_curso_id`, `carga_maxima`) VALUES
(40, 'Bolsa_Projeto_de_Ensino_e_Extensões', 4, 40),
(41, 'Ouvinte_em_Eventos_Relacionados_ao_Curso', 4, 60),
(42, 'Organizador_em_Eventos_Relacionados_ao_Curso', 4, 20),
(43, 'Voluntário_em_Áreas_do_Curso', 4, 20),
(44, 'Estágio_Não_Obrigatório', 4, 40),
(45, 'Publicação_Apresentação_e_Premiação_de_Trabalhos', 4, 20),
(46, 'Visitas_e_Viagens_de_Estudo_Relacionadas_ao_Curso', 4, 30),
(47, 'Curso_de_Formação_na_Área_Específica', 4, 40),
(48, 'Ouvinte_em_Apresentação_de_Trabalhos', 4, 10),
(49, 'Curso_de_Línguas', 4, 30),
(50, 'Monitor_em_Áreas_do_Curso', 4, 30),
(51, 'Participações_Artísticas_e_Institucionais', 4, 20),
(52, 'Atividades_Colegiais_Representativas', 4, 20);

-- --------------------------------------------------------

--
-- Table structure for table `certificado`
--

CREATE TABLE `certificado` (
  `nome_do_arquivo` varchar(255) NOT NULL,
  `nome_pessoal` varchar(255) NOT NULL,
  `carga_horaria` float NOT NULL,
  `fk_usuario_email` varchar(255) NOT NULL,
  `fk_categoria_id` int(11) NOT NULL,
  `status` enum('não_verificado','válido','incerto') NOT NULL DEFAULT 'não_verificado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `codigo_de_verificacao`
--

CREATE TABLE `codigo_de_verificacao` (
  `codigo` char(8) NOT NULL,
  `hora_da_criacao` timestamp NULL DEFAULT current_timestamp(),
  `fk_usuario_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curso`
--

CREATE TABLE `curso` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `curso`
--

INSERT INTO `curso` (`id`, `nome`) VALUES
(1, 'Administração'),
(2, 'Alimentos'),
(3, 'Agropecuária'),
(4, 'Informática');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `email` varchar(255) NOT NULL,
  `nome_de_usuario` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_de_conta` enum('aluno','coordenador') NOT NULL DEFAULT 'aluno',
  `fk_curso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Categoria_Curso` (`fk_curso_id`);

--
-- Indexes for table `certificado`
--
ALTER TABLE `certificado`
  ADD PRIMARY KEY (`nome_do_arquivo`),
  ADD KEY `FK_Certificado_Usuario` (`fk_usuario_email`),
  ADD KEY `FK_Certificado_Categoria` (`fk_categoria_id`);

--
-- Indexes for table `codigo_de_verificacao`
--
ALTER TABLE `codigo_de_verificacao`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `FK_Codigo_de_verificacao_2` (`fk_usuario_email`);

--
-- Indexes for table `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`email`),
  ADD KEY `FK_Usuario_Curso` (`fk_curso_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `curso`
--
ALTER TABLE `curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `FK_Categoria_Curso` FOREIGN KEY (`fk_curso_id`) REFERENCES `curso` (`id`);

--
-- Constraints for table `certificado`
--
ALTER TABLE `certificado`
  ADD CONSTRAINT `FK_Certificado_Categoria` FOREIGN KEY (`fk_categoria_id`) REFERENCES `categoria` (`id`),
  ADD CONSTRAINT `FK_Certificado_Usuario` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `codigo_de_verificacao`
--
ALTER TABLE `codigo_de_verificacao`
  ADD CONSTRAINT `FK_Codigo_de_verificacao_2` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_Usuario_Curso` FOREIGN KEY (`fk_curso_id`) REFERENCES `curso` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

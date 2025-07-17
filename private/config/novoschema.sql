-- phpMyAdmin SQL Dump
-- version 5.2.2-1.fc42
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 15, 2025 at 09:40 PM
-- Server version: 10.11.11-MariaDB
-- PHP Version: 8.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ChronoCert`
--

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `email` varchar(255) NOT NULL,
  `nome_de_usuario` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_de_conta` enum('aluno','coordenador') NOT NULL DEFAULT 'aluno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`email`);

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categoria`
--
-- (Estes são os valores que estavam fixos anteriormente)
--
INSERT INTO `categoria` (`nome`) VALUES
('Bolsa_Projetos_de_Ensino_e_Extensoes'),
('Ouvinte_em_Eventos_relacionados_ao_Curso'),
('Organizador_em_Eventos_relacionados_ao_Curso'),
('Voluntario_em_Areas_do_Curso'),
('Estagio_Nao_Obrigatorio'),
('Publicacao_Apresentacao_e_Premiacao_de_Trabalhos'),
('Visitas_e_Viagens_de_Estudo_relacionadas_ao_Curso'),
('Curso_de_Formacao_na_Area_Especifica'),
('Ouvinte_em_apresentacao_de_trabalhos'),
('Curso_de_Linguas'),
('Monitor_em_Areas_do_Curso'),
('Participacoes_Artisticas_e_Institucionais'),
('Atividades_Colegiais_Representativas');

-- --------------------------------------------------------

--
-- Table structure for table `certificado`
--

CREATE TABLE `certificado` (
  `nome_do_arquivo` varchar(255) NOT NULL,
  `nome_pessoal` varchar(255) NOT NULL,
  `carga_horaria` float NOT NULL,
  `fk_usuario_email` varchar(256) NOT NULL,
  `fk_categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `certificado`
--
ALTER TABLE `certificado`
  ADD PRIMARY KEY (`nome_do_arquivo`),
  ADD KEY `FK_Certificado_Usuario` (`fk_usuario_email`),
  ADD KEY `FK_Certificado_Categoria` (`fk_categoria_id`);

--
-- Constraints for table `certificado`
--
ALTER TABLE `certificado`
  ADD CONSTRAINT `FK_Certificado_Categoria` FOREIGN KEY (`fk_categoria_id`) REFERENCES `categoria` (`id`),
  ADD CONSTRAINT `FK_Certificado_Usuario` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `codigo_de_verificacao`
--

CREATE TABLE `codigo_de_verificacao` (
  `codigo` char(8) NOT NULL,
  `hora_da_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fk_usuario_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `codigo_de_verificacao`
--
ALTER TABLE `codigo_de_verificacao`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `FK_Codigo_de_verificacao_2` (`fk_usuario_email`);

--
-- Constraints for table `codigo_de_verificacao`
--
ALTER TABLE `codigo_de_verificacao`
  ADD CONSTRAINT `FK_Codigo_de_verificacao_2` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;

-- --------------------------------------------------------

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `limpar_codigos_expirados` ON SCHEDULE EVERY 1 SECOND STARTS '2025-01-09 16:43:26' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM codigo_de_verificacao WHERE hora_da_criacao < (NOW() - INTERVAL 3 MINUTE)$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
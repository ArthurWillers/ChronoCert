-- phpMyAdmin SQL Dump
-- version 5.2.2-1.fc42
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 21, 2025 at 01:59 PM
-- Server version: 10.11.11-MariaDB
-- PHP Version: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `chronocert`
--

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE IF NOT EXISTS `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `fk_curso_id` int(11) NOT NULL,
  `carga_maxima` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_Categoria_Curso` (`fk_curso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`nome`, `fk_curso_id`, `carga_maxima`) VALUES
('Bolsa_Projeto_de_Ensino_e_Extensões', 4, 40),
('Ouvinte_em_Eventos_Relacionados_ao_Curso', 4, 60),
('Organizador_em_Eventos_Relacionados_ao_Curso', 4, 20),
('Voluntário_em_Áreas_do_Curso', 4, 20),
('Estágio_Não_Obrigatório', 4, 40),
('Publicação_Apresentação_e_Premiação_de_Trabalhos', 4, 20),
('Visitas_e_Viagens_de_Estudo_Relacionadas_ao_Curso', 4, 30),
('Curso_de_Formação_na_Área_Específica', 4, 40),
('Ouvinte_em_Apresentação_de_Trabalhos', 4, 10),
('Curso_de_Línguas', 4, 30),
('Monitor_em_Áreas_do_Curso', 4, 30),
('Participações_Artísticas_e_Institucionais', 4, 20),
('Atividades_Colegiais_Representativas', 4, 20);

-- --------------------------------------------------------

--
-- Table structure for table `certificado`
--

CREATE TABLE IF NOT EXISTS `certificado` (
  `nome_do_arquivo` varchar(255) NOT NULL,
  `nome_pessoal` varchar(255) NOT NULL,
  `carga_horaria` float NOT NULL,
  `fk_usuario_email` varchar(255) NOT NULL,
  `fk_categoria_id` int(11) NOT NULL,
  `status` enum('não_verificado','válido','incerto') NOT NULL DEFAULT 'não_verificado',
  PRIMARY KEY (`nome_do_arquivo`),
  KEY `FK_Certificado_Usuario` (`fk_usuario_email`),
  KEY `FK_Certificado_Categoria` (`fk_categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `codigo_de_verificacao`
--

CREATE TABLE IF NOT EXISTS `codigo_de_verificacao` (
  `codigo` char(8) NOT NULL,
  `hora_da_criacao` timestamp NULL DEFAULT current_timestamp(),
  `fk_usuario_email` varchar(255) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK_Codigo_de_verificacao_2` (`fk_usuario_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curso`
--

CREATE TABLE IF NOT EXISTS `curso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
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

CREATE TABLE IF NOT EXISTS `usuario` (
  `email` varchar(255) NOT NULL,
  `nome_de_usuario` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_de_conta` enum('aluno','coordenador') NOT NULL DEFAULT 'aluno',
  `fk_curso_id` int(11) NOT NULL,
  PRIMARY KEY (`email`),
  KEY `FK_Usuario_Curso` (`fk_curso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `cleanup_expired_codes` ON SCHEDULE EVERY 1 SECOND STARTS '2025-07-21 13:51:54' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  DELETE FROM codigo_de_verificacao 
  WHERE TIMESTAMPDIFF(MINUTE, hora_da_criacao, NOW()) > 3;
END$$

DELIMITER ;
COMMIT;
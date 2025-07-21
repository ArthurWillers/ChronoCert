SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Estrutura para tabela `categoria`
CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `fk_curso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Despejando dados para a tabela `categoria`
INSERT INTO `categoria` (`id`, `nome`, `fk_curso_id`) VALUES
(1, 'Bolsa_Projeto_de_Ensino_e_Extensões', 1),
(2, 'Ouvinte_em_Eventos_Relacionados_ao_Curso', 1),
(3, 'Organizador_em_Eventos_Relacionados_ao_Curso', 1),
(4, 'Voluntário_em_Áreas_do_Curso', 1),
(5, 'Estágio_Não_Obrigatório', 1),
(6, 'Publicação_Apresentação_e_Premiação_de_Trabalhos', 1),
(7, 'Visitas_e_Viagens_de_Estudo_Relacionadas_ao_Curso', 1),
(8, 'Curso_de_Formação_na_Área_Específica', 1),
(9, 'Ouvinte_em_Apresentação_de_Trabalhos', 1),
(10, 'Curso_de_Línguas', 1),
(11, 'Monitor_em_Áreas_do_Curso', 1),
(12, 'Participações_Artísticas_e_Institucionais', 1),
(13, 'Atividades_Colegiais_Representativas', 1),
(14, 'Bolsa_Projeto_de_Ensino_e_Extensões', 2),
(15, 'Ouvinte_em_Eventos_Relacionados_ao_Curso', 2),
(16, 'Organizador_em_Eventos_Relacionados_ao_Curso', 2),
(17, 'Voluntário_em_Áreas_do_Curso', 2),
(18, 'Estágio_Não_Obrigatório', 2),
(19, 'Publicação_Apresentação_e_Premiação_de_Trabalhos', 2),
(20, 'Visitas_e_Viagens_de_Estudo_Relacionadas_ao_Curso', 2),
(21, 'Curso_de_Formação_na_Área_Específica', 2),
(22, 'Ouvinte_em_Apresentação_de_Trabalhos', 2),
(23, 'Curso_de_Línguas', 2),
(24, 'Monitor_em_Áreas_do_Curso', 2),
(25, 'Participações_Artísticas_e_Institucionais', 2),
(26, 'Atividades_Colegiais_Representativas', 2),
(27, 'Bolsa_Projeto_de_Ensino_e_Extensões', 3),
(28, 'Ouvinte_em_Eventos_Relacionados_ao_Curso', 3),
(29, 'Organizador_em_Eventos_Relacionados_ao_Curso', 3),
(30, 'Voluntário_em_Áreas_do_Curso', 3),
(31, 'Estágio_Não_Obrigatório', 3),
(32, 'Publicação_Apresentação_e_Premiação_de_Trabalhos', 3),
(33, 'Visitas_e_Viagens_de_Estudo_Relacionadas_ao_Curso', 3),
(34, 'Curso_de_Formação_na_Área_Específica', 3),
(35, 'Ouvinte_em_Apresentação_de_Trabalhos', 3),
(36, 'Curso_de_Línguas', 3),
(37, 'Monitor_em_Áreas_do_Curso', 3),
(38, 'Participações_Artísticas_e_Institucionais', 3),
(39, 'Atividades_Colegiais_Representativas', 3),
(40, 'Bolsa_Projeto_de_Ensino_e_Extensões', 4),
(41, 'Ouvinte_em_Eventos_Relacionados_ao_Curso', 4),
(42, 'Organizador_em_Eventos_Relacionados_ao_Curso', 4),
(43, 'Voluntário_em_Áreas_do_Curso', 4),
(44, 'Estágio_Não_Obrigatório', 4),
(45, 'Publicação_Apresentação_e_Premiação_de_Trabalhos', 4),
(46, 'Visitas_e_Viagens_de_Estudo_Relacionadas_ao_Curso', 4),
(47, 'Curso_de_Formação_na_Área_Específica', 4),
(48, 'Ouvinte_em_Apresentação_de_Trabalhos', 4),
(49, 'Curso_de_Línguas', 4),
(50, 'Monitor_em_Áreas_do_Curso', 4),
(51, 'Participações_Artísticas_e_Institucionais', 4),
(52, 'Atividades_Colegiais_Representativas', 4);

-- Estrutura para tabela `certificado`
CREATE TABLE `certificado` (
  `nome_do_arquivo` varchar(255) NOT NULL,
  `nome_pessoal` varchar(255) NOT NULL,
  `carga_horaria` float NOT NULL,
  `fk_usuario_email` varchar(255) NOT NULL,
  `fk_categoria_id` int(11) NOT NULL,
  `status` enum('não_verificado','válido', 'incerto') NOT NULL DEFAULT 'não_verificado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura para tabela `codigo_de_verificacao`
CREATE TABLE `codigo_de_verificacao` (
  `codigo` char(8) NOT NULL,
  `hora_da_criacao` timestamp NULL DEFAULT current_timestamp(),
  `fk_usuario_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura para tabela `curso`
CREATE TABLE `curso` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Despejando dados para a tabela `curso`
INSERT INTO `curso` (`id`, `nome`) VALUES
(1, 'Administração'),
(2, 'Alimentos'),
(3, 'Agropecuária'),
(4, 'Informática');

-- Estrutura para tabela `usuario`
CREATE TABLE `usuario` (
  `email` varchar(255) NOT NULL,
  `nome_de_usuario` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_de_conta` enum('aluno','coordenador') NOT NULL DEFAULT 'aluno',
  `fk_curso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices de tabela `categoria`
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_Categoria_Curso` (`fk_curso_id`);

-- Índices de tabela `certificado`
ALTER TABLE `certificado`
  ADD PRIMARY KEY (`nome_do_arquivo`),
  ADD KEY `FK_Certificado_Usuario` (`fk_usuario_email`),
  ADD KEY `FK_Certificado_Categoria` (`fk_categoria_id`);

-- Índices de tabela `codigo_de_verificacao`
ALTER TABLE `codigo_de_verificacao`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `FK_Codigo_de_verificacao_2` (`fk_usuario_email`);

-- Índices de tabela `curso`
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id`);

-- Índices de tabela `usuario`
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`email`),
  ADD KEY `FK_Usuario_Curso` (`fk_curso_id`);

-- AUTO_INCREMENT de tabela `categoria`
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

-- AUTO_INCREMENT de tabela `curso`
ALTER TABLE `curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- Restrições para tabelas `categoria`
ALTER TABLE `categoria`
  ADD CONSTRAINT `FK_Categoria_Curso` FOREIGN KEY (`fk_curso_id`) REFERENCES `curso` (`id`);

-- Restrições para tabelas `certificado`
ALTER TABLE `certificado`
  ADD CONSTRAINT `FK_Certificado_Categoria` FOREIGN KEY (`fk_categoria_id`) REFERENCES `categoria` (`id`),
  ADD CONSTRAINT `FK_Certificado_Usuario` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;

-- Restrições para tabelas `codigo_de_verificacao`
ALTER TABLE `codigo_de_verificacao`
  ADD CONSTRAINT `FK_Codigo_de_verificacao_2` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;

-- Restrições para tabelas `usuario`
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_Usuario_Curso` FOREIGN KEY (`fk_curso_id`) REFERENCES `curso` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
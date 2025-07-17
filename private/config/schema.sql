SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Estrutura para tabela `categoria`

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Inserção de dados na tabela `categoria`
INSERT INTO `categoria` (`id`, `nome`) VALUES
(1, 'Bolsa_Projetos_de_Ensino_e_Extensoes'),
(2, 'Ouvinte_em_Eventos_relacionados_ao_Curso'),
(3, 'Organizador_em_Eventos_relacionados_ao_Curso'),
(4, 'Voluntario_em_Areas_do_Curso'),
(5, 'Estagio_Nao_Obrigatorio'),
(6, 'Publicacao_Apresentacao_e_Premiacao_de_Trabalhos'),
(7, 'Visitas_e_Viagens_de_Estudo_relacionadas_ao_Curso'),
(8, 'Curso_de_Formacao_na_Area_Especifica'),
(9, 'Ouvinte_em_apresentacao_de_trabalhos'),
(10, 'Curso_de_Linguas'),
(11, 'Monitor_em_Areas_do_Curso'),
(12, 'Participacoes_Artisticas_e_Institucionais'),
(13, 'Atividades_Colegiais_Representativas');

-- Estrutura para tabela `certificado`
CREATE TABLE `certificado` (
  `nome_do_arquivo` varchar(255) NOT NULL,
  `nome_pessoal` varchar(255) NOT NULL,
  `carga_horaria` float NOT NULL,
  `fk_usuario_email` varchar(256) NOT NULL,
  `fk_categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura para tabela `codigo_de_verificacao`
CREATE TABLE `codigo_de_verificacao` (
  `codigo` char(8) NOT NULL,
  `hora_da_criacao` timestamp NULL DEFAULT current_timestamp(),
  `fk_usuario_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura para tabela `usuario`
CREATE TABLE `usuario` (
  `email` varchar(255) NOT NULL,
  `nome_de_usuario` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_de_conta` enum('aluno','coordenador') NOT NULL DEFAULT 'aluno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices de tabela `categoria`
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

-- Índices de tabela `certificado`
ALTER TABLE `certificado`
  ADD PRIMARY KEY (`nome_do_arquivo`),
  ADD KEY `FK_Certificado_Usuario` (`fk_usuario_email`),
  ADD KEY `FK_Certificado_Categoria` (`fk_categoria_id`);

-- Índices de tabela `codigo_de_verificacao`
ALTER TABLE `codigo_de_verificacao`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `FK_Codigo_de_verificacao_2` (`fk_usuario_email`);

-- Índices de tabela `usuario`
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`email`);

-- AUTO_INCREMENT de tabela `categoria`
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Restrições para tabelas `certificado`
ALTER TABLE `certificado`
  ADD CONSTRAINT `FK_Certificado_Categoria` FOREIGN KEY (`fk_categoria_id`) REFERENCES `categoria` (`id`),
  ADD CONSTRAINT `FK_Certificado_Usuario` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;

-- Restrições para tabelas `codigo_de_verificacao`
ALTER TABLE `codigo_de_verificacao`
  ADD CONSTRAINT `FK_Codigo_de_verificacao_2` FOREIGN KEY (`fk_usuario_email`) REFERENCES `usuario` (`email`) ON DELETE CASCADE;
COMMIT;
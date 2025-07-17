INSERT IGNORE INTO `categoria` (`nome`) VALUES
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

UPDATE certificado c 
JOIN categoria cat ON FIND_IN_SET(cat.nome, c.categoria) > 0
SET c.fk_categoria_id = cat.id
WHERE c.fk_categoria_id IS NULL OR c.fk_categoria_id = 0;

UPDATE usuario SET tipo_de_conta = 'aluno' WHERE tipo_de_conta IS NULL;

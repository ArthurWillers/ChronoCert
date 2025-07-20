# ChronoCert

Sistema web para gerenciamento de certificados acadêmicos, desenvolvido como trabalho acadêmico para o **Instituto Federal Farroupilha - Campus Santo Augusto**, especificamente para o **Curso Técnico em Informática Integrado ao Ensino Médio**.

> **Nota**: Este projeto foi desenvolvido exclusivamente para atender às necessidades do Curso Técnico em Informática Integrado ao Ensino Médio do IFFar Campus Santo Augusto, seguindo as diretrizes específicas de atividades complementares do curso.

## Sobre o Projeto

O ChronoCert permite o upload, organização por categorias e controle de carga horária de certificados acadêmicos, facilitando o gerenciamento das atividades complementares exigidas pelo curso.

## Funcionalidades

- **Autenticação de usuários**: Registro, login e recuperação de senha
- **Gerenciamento de certificados**: Upload, visualização, download e exclusão
- **Categorização**: Organização por 13 categorias acadêmicas diferentes
- **Controle de carga horária**: Acompanhamento do progresso por categoria
- **Download em lote**: Baixar todos os certificados em um arquivo ZIP
- **Recuperação de senha**: Sistema de código de verificação por email
- **Interface responsiva**: Design adaptável para diferentes dispositivos

## Tecnologias Utilizadas

- **Backend**: PHP 8.4+
- **Banco de dados**: MariaDB/MySQL 10.11+
- **Frontend**: Bootstrap 5.3.6, HTML5, CSS3, JavaScript
- **Gerenciamento de dependências**: Composer
- **Bibliotecas**:
  - PHPMailer 6.10+ (envio de emails)
  - vlucas/phpdotenv 5.6+ (variáveis de ambiente)

## Contexto Acadêmico

### Instituto Federal Farroupilha - Campus Santo Augusto
**Curso**: Técnico em Informática Integrado ao Ensino Médio

Este sistema foi desenvolvido para atender especificamente às diretrizes de atividades complementares do curso, permitindo que os estudantes organizem e controlem suas horas de atividades extracurriculares de forma eficiente.

## Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/ArthurWillers/ChronoCert.git
cd ChronoCert
```

### 2. Instale as dependências do Composer

```bash
composer install
```

### 3. Configure o banco de dados

Crie um banco de dados MariaDB/MySQL e importe o schema:

```bash
mysql -u root -p -e "CREATE DATABASE ChronoCert CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p ChronoCert < private/config/schema.sql
```

### 4. Configure as variáveis de ambiente

Copie o arquivo de exemplo e configure suas credenciais:

```bash
cp private/config/.env.example private/config/.env
```

Edite o arquivo `private/config/.env` com suas configurações:

```bash
# DB Connection
DB_HOST=localhost
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
DB_NAME=ChronoCert

# SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=465
SMTP_USER=seu_email@gmail.com
SMTP_PASS="sua_senha_app"
```

### 5. Crie as pastas necessárias e configure as permissões

```bash
mkdir -p /var/www/html/ChronoCert/private/uploads
mkdir -p /var/www/html/ChronoCert/private/tmp

sudo chown -R www-data:www-data /var/www/html/ChronoCert/private/uploads
sudo chmod -R 775 /var/www/html/ChronoCert/private/uploads

sudo chown -R www-data:www-data /var/www/html/ChronoCert/private/tmp
sudo chmod -R 775 /var/www/html/ChronoCert/private/tmp
```

### 6. Configure o servidor web

#### Apache

Adicione às configurações do apache:

```apache
<Directory /var/www/html/ChronoCert/private>
    AllowOverride None
    Require all denied
</Directory>
```

### 7. Configure o SELinux (se aplicável)

```bash
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/ChronoCert/private/uploads
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/ChronoCert/private/tmp
```

### 8. Criação de usuário coordenador

Para criar um usuário coordenador, faça uma inserção na tabela `usuario` do banco de dados, e, no campo senha, ponha o resultado do seguinte comando, substituindo `[SENHA]` pela senha desejada:

```
php private/config/senha-coordenador.php [SENHA]
```

## Configuração de Email

Para o sistema de recuperação de senha funcionar, configure um provedor SMTP:

### Gmail
1. Ative a autenticação de dois fatores
2. Gere uma senha de aplicativo
3. Use a senha de aplicativo no arquivo `.env`

### Outros provedores
Consulte a documentação do seu provedor SMTP para configurações específicas.

## Categorias de Certificados

O sistema, por padrão, tem 13 categorias em 4 cursos com limites de carga horária conforme diretrizes acadêmicas:

1. **Bolsa, Projetos de Ensino e Extensões** (40h)
2. **Ouvinte em Eventos relacionados ao Curso** (60h)
3. **Organizador em Eventos relacionados ao Curso** (20h)
4. **Voluntário em Áreas do Curso** (20h)
5. **Estágio Não Obrigatório** (40h)
6. **Publicação, Apresentação e Premiação de Trabalhos** (20h)
7. **Visitas e Viagens de Estudo relacionadas ao Curso** (30h)
8. **Curso de Formação na Área Específica** (40h)
9. **Ouvinte em Apresentação de Trabalhos** (10h)
10. **Curso de Línguas** (30h)
11. **Monitor em Áreas do Curso** (30h)
12. **Participações Artísticas e Institucionais** (20h)
13. **Atividades Colegiais Representativas** (20h)

## Segurança

- Senhas são criptografadas com bcrypt
- Arquivos são armazenados fora do diretório web público
- Validação de tipo MIME para uploads
- Proteção contra acesso direto a arquivos sensíveis
- Sessões seguras com nome personalizado
- Códigos de verificação com expiração automática (3 minutos)

## Manutenção

### Limpeza automática
O banco possui um evento que remove códigos de verificação expirados automaticamente a cada segundo (o evento não vem ativado por padrão).

## Troubleshooting

### Problemas comuns

**Erro de conexão com banco:**
- Verifique as credenciais no arquivo `.env`
- Confirme se o banco de dados existe
- Teste a conectividade: `mysql -u usuario -p -h host`

**Upload de arquivos falha:**
- Verifique permissões da pasta `private/uploads/`
- Confirme `upload_max_filesize` e `post_max_size` no PHP
- Verifique espaço em disco

**Emails não são enviados:**
- Teste configurações SMTP
- Verifique logs do servidor
- Confirme se as portas estão abertas


## Finalidade Acadêmica

Este projeto foi desenvolvido como trabalho acadêmico e possui as seguintes características:

- ✅ **Escopo definido**: Focado especificamente no Curso Técnico em Informática Integrado
- ✅ **Categorias customizadas**: Baseadas nas diretrizes do curso
- ✅ **Limites de carga horária**: Conforme regulamentação acadêmica

---

**Desenvolvido por**: Arthur Vinicius Willers, Gabriel Dill Panzenhagen, Leonardo Miguel Bandeira e Vitor Mateus Guerra da Silva  
**Instituição**: Instituto Federal Farroupilha - Campus Santo Augusto  
**Curso**: Técnico em Informática Integrado ao Ensino Médio  
**Ano**: 2025

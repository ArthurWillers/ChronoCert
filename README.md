# ChronoCert

Sistema web para gerenciamento de certificados acadêmicos, desenvolvido como trabalho acadêmico para o **Instituto Federal Farroupilha - Campus Santo Augusto**, especificamente para o **Curso Técnico em Informática Integrado ao Ensino Médio**.

> **Nota**: Este projeto foi desenvolvido exclusivamente para atender às necessidades do Curso Técnico em Informática Integrado ao Ensino Médio do IFFar Campus Santo Augusto, seguindo as diretrizes específicas de atividades complementares do curso.

## Sobre o Projeto

O ChronoCert permite o upload, organização por categorias e controle de carga horária de certificados acadêmicos, facilitando o gerenciamento das atividades complementares exigidas pelo curso.

## Funcionalidades

- **Autenticação de usuários**: Registro, login e recuperação de senha
- **Gerenciamento de certificados**: Upload, visualização, download e exclusão
- **Categorização**: Organização por categorias acadêmicas diferentes por curso
- **Controle de carga horária**: Acompanhamento do progresso por categoria
- **Download em lote**: Baixar todos os certificados em um arquivo ZIP
- **Recuperação de senha**: Sistema de código de verificação por email
- **Interface responsiva**: Design adaptável para diferentes dispositivos

## Requisitos do Sistema

### Requisitos mínimos
- **PHP**: 8.1 ou superior
- **Banco de dados**: MariaDB 10.6+ ou MySQL 8.0+
- **Servidor web**: Apache 2.4+ ou Nginx 1.18+
- **Extensões PHP necessárias**:
  - `mysqli`
  - `mbstring`
  - `fileinfo`
  - `zip`
  - `openssl`

### Configurações PHP recomendadas
```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
memory_limit = 256M
session.cookie_secure = 1
session.cookie_httponly = 1
```

## Tecnologias Utilizadas

- **Backend**: PHP 8.1+
- **Banco de dados**: MariaDB 10.6+ / MySQL 8.0+
- **Frontend**: Bootstrap 5.3.6, HTML5, CSS3, JavaScript
- **Gerenciamento de dependências**: Composer
- **Bibliotecas principais**:
  - PHPMailer 6.10+ (envio de emails)
  - vlucas/phpdotenv 5.6+ (gerenciamento de variáveis de ambiente)

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

```env
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

### 5. Configure as permissões para as pastas necessárias

```bash
# Ubuntu/Debian
sudo chown -R www-data:www-data private/uploads private/tmp
sudo chmod -R 775 private/uploads private/tmp

# Fedora/RHEL/CentOS
sudo chown -R apache:apache private/uploads private/tmp
sudo chmod -R 775 private/uploads private/tmp
```

### 6. Configure o servidor web

#### Apache
Adicione ao arquivo de configuração do Apache para proteger diretórios privados:

```apache
<Directory "/var/www/html/ChronoCert/private">
    AllowOverride None
    Require all denied
</Directory>
```

#### Nginx
Para Nginx, adicione ao seu arquivo de configuração:

```nginx
location /ChronoCert/private {
    deny all;
    return 404;
}
```

### 7. Configure o SELinux (se aplicável)

Se você estiver usando um sistema com SELinux ativado (como RHEL/CentOS), execute:

```bash
setsebool -P httpd_can_network_connect 1
chcon -R -t httpd_sys_rw_content_t private/uploads
chcon -R -t httpd_sys_rw_content_t private/tmp
```

### 8. Criação de usuário coordenador

Para criar um usuário coordenador, você deve inserir um registro na tabela `usuario` do banco de dados. Para a senha, use o hash gerado pelo seguinte comando:

```bash
php private/config/senha-coordenador.php [SUA_SENHA]
```

Em seguida, faça uma inserção SQL como esta:

```sql
INSERT INTO usuario (email, nome_de_usuario, senha, tipo_de_conta, fk_curso_id) 
VALUES ('coordenador@exemplo.com', 'Nome do Coordenador', 'HASH_GERADO_PELO_SCRIPT', 'coordenador', 4);
```

> **Nota**: Substitua `fk_curso_id` pelo ID do curso correspondente:
> - 1 = Administração
> - 2 = Alimentos  
> - 3 = Agropecuária
> - 4 = Informática (com categorias pré-cadastradas)

## Configuração de Email

Para o sistema de recuperação de senha funcionar, você precisa configurar um provedor SMTP.

### Gmail
1. **Ative a autenticação de dois fatores** na sua conta Google
2. **Gere uma senha de aplicativo**:
   - Acesse [myaccount.google.com](https://myaccount.google.com)
   - Vá em "Segurança" → "Senhas de app"
   - Selecione "App" → "Outro" e digite "ChronoCert"
   - Use a senha gerada no arquivo `.env`

3. **Configure o arquivo `.env`**:
   ```env
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=465
   SMTP_USER=seuemail@gmail.com
   SMTP_PASS="senha_de_aplicativo_gerada"
   ```

### Outros provedores SMTP
Consulte a documentação do seu provedor

### Teste de configuração
Para testar se o email está funcionando, você pode usar o próprio sistema de recuperação de senha ou executar um teste manual.

## Categorias de Certificados

O sistema suporta múltiplos cursos técnicos, cada um com suas próprias categorias de atividades complementares. Atualmente, as categorias estão pré-cadastradas apenas para o **Curso Técnico em Informática**.

### Cursos disponíveis no sistema:
- **Administração** (ID: 1) - *sem categorias pré-cadastradas*
- **Alimentos** (ID: 2) - *sem categorias pré-cadastradas*
- **Agropecuária** (ID: 3) - *sem categorias pré-cadastradas*
- **Informática** (ID: 4) - *com 13 categorias pré-cadastradas*

### Categorias do Curso de Informática:
1. **Bolsa, Projetos de Ensino e Extensões** - 40h
2. **Ouvinte em Eventos Relacionados ao Curso** - 60h
3. **Organizador em Eventos Relacionados ao Curso** - 20h
4. **Voluntário em Áreas do Curso** - 20h
5. **Estágio Não Obrigatório** - 40h
6. **Publicação, Apresentação e Premiação de Trabalhos** - 20h
7. **Visitas e Viagens de Estudo Relacionadas ao Curso** - 30h
8. **Curso de Formação na Área Específica** - 40h
9. **Ouvinte em Apresentação de Trabalhos** - 10h
10. **Curso de Línguas** - 30h
11. **Monitor em Áreas do Curso** - 30h
12. **Participações Artísticas e Institucionais** - 20h
13. **Atividades Colegiais Representativas** - 20h

> **Total máximo de horas para Informática**: 380 horas

### Adicionando categorias para outros cursos
Para adicionar categorias aos outros cursos, crie um usuario coordenador para o curso, entre no sistema e utilize a interface de gerenciar categorias.

### Adicionando novo curso
Para adicionar um novo curso ao sistema, insira um registro na tabela `curso` do banco de dados. Exemplo:

```sql
INSERT INTO curso (nome) VALUES ('Nome do Curso');
```

## Segurança

- Senhas são hashed com bcrypt
- Arquivos são armazenados fora do diretório web público
- Validação de tipo MIME para uploads
- Proteção contra acesso direto a arquivos sensíveis
- Sessões seguras com nome personalizado
- Códigos de verificação com expiração automática (3 minutos)

## Manutenção

### Limpeza automática de códigos de verificação
O banco possui um evento automático que remove códigos de verificação expirados a cada segundo. Por padrão, este evento não vem ativado. Para ativá-lo:

```sql
-- Ativar o scheduler de eventos
SET GLOBAL event_scheduler = ON;

-- Criar evento de limpeza (se não existir)
CREATE EVENT IF NOT EXISTS cleanup_expired_codes
ON SCHEDULE EVERY 1 SECOND
DO
  DELETE FROM codigo_de_verificacao 
  WHERE TIMESTAMPDIFF(MINUTE, hora_da_criacao, NOW()) > 3;
```

### Backup do banco de dados
```bash
# Backup completo
mysqldump -u [usuario] -p ChronoCert > backup_chronocert_$(date +%Y%m%d_%H%M%S).sql

# Backup apenas da estrutura
mysqldump -u [usuario] -p --no-data ChronoCert > schema_backup.sql

# Backup apenas dos dados
mysqldump -u [usuario] -p --no-create-info ChronoCert > data_backup.sql
```

## Troubleshooting

### Problemas comuns e soluções

#### Erro de conexão com banco de dados
```bash
# Verifique as credenciais no arquivo .env
cat private/config/.env

# Teste a conectividade manualmente
mysql -u [usuario] -p -h [host] [database]

# Verifique se o serviço MySQL/MariaDB está rodando
systemctl status mysql
# ou
systemctl status mariadb
```

#### Upload de arquivos falha
```bash
# Verifique permissões
ls -la private/uploads/
ls -la private/tmp/

# Redefina permissões se necessário
# Ubuntu/Debian
chmod -R 775 private/uploads private/tmp
chown -R www-data:www-data private/uploads private/tmp

# Fedora/RHEL/CentOS
chmod -R 775 private/uploads private/tmp
chown -R apache:apache private/uploads private/tmp

# Verifique configurações do PHP
php -i | grep upload_max_filesize
php -i | grep post_max_size
php -i | grep max_execution_time
```

#### Emails não são enviados
```bash
# Verifique os logs do PHP
tail -f /var/log/php/error.log

# Teste conectividade SMTP
telnet smtp.gmail.com 465

# Verifique configurações do firewall
ufw status
# ou
iptables -L
```

#### Problemas de sessão
```bash
# Verifique configurações de sessão do PHP
php -i | grep session

# Limpe arquivos de sessão antigos
rm -rf /tmp/sess_*

# Verifique permissões do diretório de sessão
ls -la /tmp/
```

---

**Desenvolvido por**: Arthur Vinicius Willers, Gabriel Dill Panzenhagen, Leonardo Miguel Bandeira e Vitor Mateus Guerra da Silva  
**Instituição**: Instituto Federal Farroupilha - Campus Santo Augusto  
**Curso**: Técnico em Informática Integrado ao Ensino Médio  
**Ano**: 2025

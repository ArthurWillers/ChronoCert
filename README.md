# ChronoCert

Sistema web para gerenciamento de certificados acadêmicos, desenvolvido como um projeto acadêmico para o Instituto Federal Farroupilha - Campus Santo Augusto.

## Status do Projeto

> **⚠️ Atenção:** Este é um projeto acadêmico que foi descontinuado. Ele possui algumas inconsistências e funcionalidades não finalizadas, e não receberá futuras atualizações. O código está disponível como um portfólio e exemplo de desenvolvimento.

## Sobre o Projeto

O ChronoCert é uma plataforma flexível que permite a alunos e coordenadores de qualquer curso gerenciarem certificados de atividades complementares. O sistema foi desenhado para que cada coordenador possa customizar as categorias de certificados de acordo com as necessidades do seu curso, centralizando o controle e a validação das horas extracurriculares.

## Funcionalidades

### Para Alunos
- **Autenticação de usuários:** Login e recuperação de senha
- **Gerenciamento de certificados:** Upload, visualização, download e exclusão
- **Categorização:** Organização dos certificados nas categorias criadas pelo coordenador do curso
- **Controle de carga horária:** Acompanhamento do progresso de horas por categoria
- **Download em lote:** Baixar todos os certificados em um único arquivo ZIP

### Para Coordenadores
- **Cadastro de Alunos:** Coordenadores podem cadastrar novos alunos em seu respectivo curso
- **Gerenciamento de Categorias:** Liberdade para criar, editar e excluir as categorias de atividades complementares, definindo a carga horária máxima para cada uma
- **Administração de Certificados:** Visualizar e validar os certificados enviados pelos alunos
- **Visualização de Alunos:** Acompanhar o progresso de todos os alunos do curso

## Tecnologias Utilizadas

- **Backend:** PHP 8.1+
- **Banco de dados:** MariaDB 10.6+ / MySQL 8.0+
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3
- **Gerenciamento de dependências:** Composer

### Bibliotecas principais:
- PHPMailer (envio de emails)
- phpdotenv (gerenciamento de variáveis de ambiente)

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
Crie um banco de dados MariaDB/MySQL e importe o schema inicial. O schema inclui alguns cursos como exemplo, mas você pode adicionar novos.

```bash
# Crie o banco de dados
mysql -u seu_usuario -p -e "CREATE DATABASE ChronoCert CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importe a estrutura e os dados iniciais
mysql -u seu_usuario -p ChronoCert < private/config/schema.sql
```

### 4. Configure as variáveis de ambiente
Copie o arquivo de exemplo e preencha com suas credenciais:

```bash
cp private/config/.env.example private/config/.env
```

Edite o arquivo `private/config/.env`:

```env
# Conexão com o Banco de Dados
DB_HOST=localhost
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
DB_NAME=ChronoCert

# Configuração de E-mail (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=465
SMTP_USER=seu_email@gmail.com
SMTP_PASS="sua_senha_de_app"
```

### 5. Configure as permissões de pasta
O servidor web precisa de permissão de escrita nos diretórios `private/uploads` e `private/tmp`. Altere o proprietário dessas pastas para o usuário do seu servidor web (ex: www-data, apache, nginx).

```bash
# Exemplo para Debian/Ubuntu (usuário www-data)
sudo chown -R www-data:www-data private/uploads private/tmp
sudo chmod -R 775 private/uploads private/tmp
```

### 6. Configure o servidor web
É importante proteger o diretório `private` para que ele não seja acessível publicamente.

#### Apache
Adicione a seguinte diretiva no arquivo de configuração do seu site:

```apache
<Directory "/caminho/para/seu/projeto/ChronoCert/private">
    AllowOverride None
    Require all denied
</Directory>
```

#### Nginx
Adicione o seguinte bloco location na configuração do seu servidor:

```nginx
location /ChronoCert/private {
    deny all;
    return 404;
}
```

### 7. Crie um usuário Coordenador
Para criar o primeiro usuário coordenador, execute o script PHP abaixo para gerar um hash da sua senha:

```bash
php private/config/senha-coordenador.php "SUA_SENHA_AQUI"
```

Em seguida, insira o usuário no banco de dados. Use o `fk_curso_id` correspondente ao curso desejado. Para adicionar um novo curso, basta inseri-lo na tabela `curso`.

```sql
INSERT INTO usuario (email, nome_de_usuario, senha, tipo_de_conta, fk_curso_id) 
VALUES ('coordenador@exemplo.com', 'Nome do Coordenador', 'HASH_GERADO_PELO_SCRIPT', 'coordenador', 1);
```

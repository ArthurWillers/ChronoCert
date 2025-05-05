# ChronoCert

## 1. Instale as dependências do Composer

Antes de iniciar o projeto, instale as dependências executando o comando abaixo na raiz do projeto:

```bash
composer install
```

## 2. Configure as permissões da pasta de uploads

Certifique-se de conceder permissões de leitura, gravação e execução (por exemplo, 775) à pasta que faz upload ou movimentação de arquivos:

```bash
sudo chown -R apache:apache /var/www/html/ChronoCert/private/uploads
sudo chmod -R 775 /var/www/html/ChronoCert/private/uploads
```

### Configure o SELinux (se aplicável)

Se o SELinux estiver habilitado, ajuste o contexto da pasta:

```bash
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/ChronoCert/private/uploads
```

### 3. Configure o servidor web

Certifique-se de que o servidor web (Apache ou Nginx) esteja configurado para apontar para o diretório do projeto. Exemplo para Apache:

```apache
    <Directory /var/www/html/ChronoCert/private>
        AllowOverride None
        Require all denied
    </Directory>
```
# lojistica

Manual de Instalação Passo a Passo
1. Pré-requisitos

Servidor web com PHP 8.1+
MySQL 5.7+ ou MariaDB
Composer instalado
Acesso ao terminal

2. Estrutura de Pastas
text/seu-projeto/
├── config/
│   ├── database.php
│   └── session.php
├── includes/
│   └── auth.php
├── public/
│   ├── index.php
│   ├── login.php
│   ├── rastreio_publico.php
│   └── ...
├── vendor/
├── install.sql
└── composer.json

3. Passos de Instalação
Passo 1: Clonar ou copiar arquivos
bashgit clone seu-repositorio.git logistica
cd logistica
Passo 2: Instalar dependências via Composer
bashcomposer require phpmailer/phpmailer bacon/bacon-qr-code dompdf/dompdf instascan/instascan
composer require dompdf/dompdf

Passo 4: Configurar banco de dados

Abra o phpMyAdmin ou terminal MySQL
Execute o conteúdo de install.sql

sqlSOURCE /caminho/para/install.sql;
Passo 4: Configurar conexão
Edite config/database.php com suas credenciais.
Passo 5: Configurar e-mail (opcional)
Em esqueci-senha.php, configure SMTP com suas credenciais.
Passo 6: Ajustar permissões
bashchmod 755 public/
chmod 644 config/*.php
Passo 7: Acessar o sistema

URL: http://seusite.com/public/
Login padrão:
E-mail:admin@empresa.com Senha:admin123


***********************************************

logistica/
├── config/
│   ├── database.php
│   └── session.php
├── includes/
│   └── auth.php          ← AQUI
├── public/
│   ├── partials/
│   │   └── navbar.php    ← AQUI
│   ├── dashboard.php
│   ├── index.php
│   ├── aparelhos.php
│   └── ...


***********************************************

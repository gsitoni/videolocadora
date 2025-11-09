# Clube da Fita - Sistema de Locadora# Locadora de Vídeos - Sistema de Gerenciamento



Sistema web desenvolvido em PHP para gerenciamento de uma locadora de filmes.Este é um sistema de gerenciamento para locadora de vídeos. Abaixo você encontrará instruções detalhadas para configurar o ambiente de desenvolvimento e importar o banco de dados.



## 📋 Pré-requisitos## 📋 Pré-requisitos



- XAMPP (com PHP e MySQL)Antes de começar, você precisa ter instalado em seu computador:

- Navegador web moderno

1. XAMPP (para Windows)

## 🚀 Instalação Rápida   - Apache (servidor web)

   - MySQL (banco de dados)

1. Clone ou baixe este repositório para a pasta `htdocs` do seu XAMPP   - PHP

2. Execute o arquivo `iniciar_banco.bat` com duplo clique

3. Acesse `http://localhost/teste_locadora` no navegador## 🚀 Passo a Passo para Configuração



## 🔑 Dados de Acesso Padrão### 1. Instalando o XAMPP



```1. Baixe o XAMPP do site oficial: [https://www.apachefriends.org/](https://www.apachefriends.org/)

Usuário Admin:2. Execute o instalador e siga as instruções

Login: admin   - Mantenha o diretório padrão (`C:\xampp`) se possível

Senha: admin123   - Você pode desmarcar componentes que não vai usar (como FileZilla, Mercury, Tomcat)

```   - Marque Apache, MySQL e PHP (essenciais)



## 🗄️ Estrutura do Banco de Dados### 2. Iniciando os Serviços



O sistema utiliza as seguintes tabelas:1. Abra o "XAMPP Control Panel"

   - Procure por "XAMPP" no menu Iniciar do Windows

### 👥 Cliente   - Ou execute: `C:\xampp\xampp-control.exe`

- Armazena informações dos clientes

- Inclui dados de login e permissões2. Inicie os serviços necessários:

- Campo `data_cadastro` automático   - Clique em "Start" para Apache

   - Clique em "Start" para MySQL

### 🎬 Filme   - ✅ Aguarde até os nomes ficarem verdes

- Cadastro completo de filmes   - ⚠️ Se der erro de porta em uso, veja a seção de Troubleshooting abaixo

- Informações como título, gênero, elenco

- Controle de estado e identificação### 3. Importando o Banco de Dados



### 👨‍💼 FuncionárioTemos um script PowerShell que automatiza todo o processo de criação e importação do banco. Para usá-lo:

- Registro de funcionários

- Dados pessoais e profissionais1. Abra o PowerShell como administrador

- Controle de cargo e turno   - Clique direito no menu Iniciar

   - Escolha "Windows PowerShell (Admin)" ou "Terminal (Admin)"

### 📋 Locação

- Gerenciamento de aluguéis2. Navegue até a pasta do projeto

- Relaciona cliente, filme e funcionário   ```powershell

- Controle de preços e descontos   cd C:\xampp\htdocs\teste_locadora

   ```

### 💰 Pagamento

- Registro financeiro das locações3. Execute o script de importação

- Controle de datas e valores   ```powershell

- Cálculo de juros e alterações   powershell -ExecutionPolicy Bypass -File .\import_db.ps1 -DropDatabase

   ```

## 🛠️ Scripts de Instalação

   O que cada parte significa:

### iniciar_banco.bat   - `-ExecutionPolicy Bypass`: permite executar o script

```batch   - `-DropDatabase`: apaga o banco se já existir (recomendado primeira vez)

Script em batch que:

- Verifica se o XAMPP está instalado4. Aguarde a conclusão

- Inicia o serviço MySQL se necessário   - Você verá mensagens em azul indicando o progresso

- Executa o script SQL de configuração   - No final, deve ver a lista de tabelas criadas

- Exibe mensagens amigáveis ao usuário

```### 🔄 Reexecutando a Importação



### setup_database.sqlSe precisar importar novamente (ex: após mudanças no SQL):

```sql

Script SQL que:1. Para fazer backup antes:

- Cria o banco de dados se não existir   ```powershell

- Configura todas as tabelas necessárias   .\import_db.ps1 -Backup -DropDatabase

- Adiciona dados iniciais de exemplo   ```

- Usa verificações de existência para evitar duplicações

```2. Para apenas reimportar (destruindo dados anteriores):

   ```powershell

## 📁 Estrutura de Arquivos   .\import_db.ps1 -DropDatabase

   ```

```

teste_locadora/3. Para importar mantendo dados (pode dar erro se estrutura mudou):

├── config.php         # Configuração do banco   ```powershell

├── index.php         # Página inicial/login   .\import_db.ps1

├── home.php          # Dashboard principal   ```

├── locadora.php      # Gestão de filmes

├── setup_database.sql # Script do banco## ❗ Troubleshooting

├── iniciar_banco.bat # Instalação automática

└── style/            # Arquivos CSS### Portas em Uso

```

Se Apache ou MySQL não iniciarem por conflito de porta:

## ⚙️ Funcionalidades Principais

1. **Erro no Apache (porta 80 ou 443)**

- Sistema de login com níveis de acesso   - Feche outros servidores web (IIS, Skype)

- Gestão de clientes e funcionários   - Ou mude a porta no arquivo `C:\xampp\apache\conf\httpd.conf`

- Cadastro e controle de filmes

- Sistema de locação com preços2. **Erro no MySQL (porta 3306)**

- Painel administrativo   - Feche outras instâncias do MySQL

- Interface responsiva   - Verifique se SQL Server não está usando a porta

   - Use o Gerenciador de Tarefas para identificar o processo

## 🔒 Segurança

### Erros Comuns no Script

- Senhas armazenadas com segurança

- Controle de sessão de usuário1. **"Não é possível executar scripts"**

- Validação de permissões   - Use o comando com `-ExecutionPolicy Bypass`

- Proteção contra SQL Injection   - Ou execute no PowerShell como admin:

     ```powershell

## 💡 Uso do Sistema     Set-ExecutionPolicy RemoteSigned

     ```

1. **Login/Cadastro**

   - Use as credenciais padrão ou crie nova conta2. **"MySQL não encontrado"**

   - Admins têm acesso a todas as funcionalidades   - Verifique se XAMPP está em `C:\xampp`

   - Ou use o parâmetro `-MysqlBin`:

2. **Navegação**     ```powershell

   - Menu superior para todas as seções     .\import_db.ps1 -MysqlBin "C:\seu\caminho\mysql\bin" -DropDatabase

   - Dashboard com ações rápidas     ```

   - Busca integrada de filmes

3. **Erro de acesso negado no MySQL**

3. **Gestão**   - Verifique se MySQL está rodando no XAMPP Control Panel

   - Cadastro de novos filmes   - Se definiu senha para root, use:

   - Controle de locações     ```powershell

   - Relatórios e histórico     .\import_db.ps1 -Password "SuaSenha" -DropDatabase

     ```

## 🚨 Solução de Problemas

## 📝 Verificando a Instalação

1. **Erro no banco de dados**

   - Verifique se o XAMPP está rodandoPara confirmar que tudo funcionou:

   - Execute o `iniciar_banco.bat` novamente

   - Confira as credenciais em `config.php`1. Abra o navegador

2. Acesse [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

2. **Página não carrega**3. Clique em "video_locadora" no menu lateral

   - Verifique se o Apache está rodando4. Você deve ver as tabelas:

   - Confirme o caminho correto na URL   - cliente

   - Limpe o cache do navegador   - filme

   - funcionario

## 🤝 Contribuição   - locacao

   - pagamento

1. Faça um Fork do projeto

2. Crie uma Branch para sua Feature## 🆘 Precisa de Ajuda?

3. Faça o Commit das mudanças

4. Faça o Push para a BranchSe encontrar problemas:

5. Abra um Pull Request

1. Verifique se os serviços estão rodando no XAMPP Control Panel

## ✨ Próximas Atualizações2. Leia as mensagens de erro com atenção

3. Consulte a seção de Troubleshooting acima

- [ ] Sistema de reservas4. Se o erro persistir, tente:

- [ ] Relatórios avançados   - Reiniciar os serviços no XAMPP

- [ ] Integração com API de filmes   - Reiniciar o computador

- [ ] Sistema de avaliações   - Verificar logs em `C:\xampp\mysql\data\mysql_error.log`

- [ ] Área do cliente aprimorada

## 🔍 Estrutura do Banco

## 📄 Licença

O banco `video_locadora` contém as seguintes tabelas:

Este projeto está sob a licença MIT - veja o arquivo LICENSE para detalhes

- `cliente`: Cadastro de clientes

## ✉️ Contato- `filme`: Catálogo de filmes

- `funcionario`: Registro de funcionários

Para sugestões ou dúvidas, entre em contato através do GitHub.- `locacao`: Controle de locações

- `pagamento`: Registro de pagamentos

---

Desenvolvido com 💜 para o Clube da FitaPara ver a estrutura detalhada, consulte o arquivo `video_locadora.sql`.

# Atualizado em 09/11/2025


@echo off
echo =============================================
echo    Instalacao do Banco Clube da Fita
echo =============================================
echo.

REM Verifica se o XAMPP está instalado
if not exist "C:\xampp\mysql\bin\mysql.exe" (
    echo ERRO: XAMPP nao encontrado em C:\xampp
    echo Por favor, instale o XAMPP primeiro.
    pause
    exit
)

REM Tenta iniciar o MySQL se não estiver rodando
echo Verificando servico MySQL...
netstat -an | find "3306" > nul
if errorlevel 1 (
    echo Iniciando MySQL...
    start "" /B "C:\xampp\mysql\bin\mysqld.exe"
    timeout /t 5
) else (
    echo MySQL ja esta rodando
)

REM Executa o script SQL
echo.
echo Configurando o banco de dados...
"C:\xampp\mysql\bin\mysql.exe" -u root < setup_database.sql

if errorlevel 0 (
    echo.
    echo =============================================
    echo    Banco de dados configurado com sucesso!
    echo =============================================
    echo.
    echo Dados de acesso admin:
    echo Usuario: admin
    echo Senha: admin123
    echo.
) else (
    echo.
    echo ERRO: Houve um problema ao configurar o banco de dados
)

echo.
echo Pressione qualquer tecla para sair...
pause > nul
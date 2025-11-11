@echo off
REM Apagar banco de dados video_locadora usando mysql do XAMPP
REM Ajuste MYSQL_BIN se seu XAMPP estiver em outro caminho
set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
set "DB_NAME=video_locadora"
set "DB_USER=root"
set "DB_PASS="

if not exist %MYSQL_BIN% (
  echo [ERRO] Nao encontrei o cliente MySQL em %MYSQL_BIN%
  echo Ajuste a variavel MYSQL_BIN no script.
  exit /b 1
)

echo Confirmando que deseja apagar o banco %DB_NAME% ...
choice /M "Deseja realmente apagar o banco %DB_NAME%?" >nul
if errorlevel 2 (
  echo Operacao cancelada.
  exit /b 0
)

echo Removendo banco %DB_NAME% ...
"%MYSQL_BIN%" -u %DB_USER% -e "DROP DATABASE IF EXISTS %DB_NAME%;" %DB_PASS%
if errorlevel 1 (
  echo [ERRO] Falha ao tentar apagar o banco.
  exit /b 1
)

echo Banco %DB_NAME% apagado (ou ja nao existia).
exit /b 0
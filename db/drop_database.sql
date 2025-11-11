-- Script para apagar o banco de dados video_locadora de forma segura
-- Usa IF EXISTS para evitar erro caso o banco já tenha sido removido
DROP DATABASE IF EXISTS video_locadora;

-- Opcional: listar bancos restantes (descomente se quiser verificar)
-- SHOW DATABASES;
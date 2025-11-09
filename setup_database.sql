-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS video_locadora;
USE video_locadora;

-- Verificar e criar tabela cliente se não existir
CREATE TABLE IF NOT EXISTS cliente (
    id_cliente INT NOT NULL AUTO_INCREMENT,
    nome_cliente VARCHAR(50) NOT NULL,
    cpf_cliente CHAR(20) NOT NULL,
    idade_cliente TINYINT NOT NULL,
    telefone_cliente CHAR(20) NOT NULL,
    email_cliente VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY cpf_cliente (cpf_cliente),
    UNIQUE KEY username (username)
);

-- Verificar e criar tabela filme se não existir
CREATE TABLE IF NOT EXISTS filme (
    id_filme INT NOT NULL AUTO_INCREMENT,
    estado_filme VARCHAR(10) NOT NULL,
    identificacao_filme INT NOT NULL,
    ident_genero CHAR(20) NOT NULL,
    ident_data DATE NOT NULL,
    ident_duracao VARCHAR(10) NOT NULL,
    ident_elenco MEDIUMTEXT NOT NULL,
    ident_titulo CHAR(50) NOT NULL,
    ident_midia VARCHAR(20) NOT NULL,
    ident_nome_diretor VARCHAR(50) NOT NULL,
    ident_class_indic VARCHAR(20) NOT NULL,
    ident_sinopse MEDIUMTEXT NOT NULL,
    PRIMARY KEY (id_filme)
);

-- Verificar e criar tabela funcionario se não existir
CREATE TABLE IF NOT EXISTS funcionario (
    id_funcionario INT NOT NULL AUTO_INCREMENT,
    idade_funcionario INT NOT NULL,
    nome_funcionario VARCHAR(50) NOT NULL,
    salario_funcionario DECIMAL(10,2) NOT NULL,
    turno_funcionario VARCHAR(50) NOT NULL,
    cargo_funcionario VARCHAR(20) NOT NULL,
    sexo_funcionario VARCHAR(10) NOT NULL,
    cpf_funcionario CHAR(20) NOT NULL,
    email_funcionario VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_funcionario)
);

-- Verificar e criar tabela locacao se não existir
CREATE TABLE IF NOT EXISTS locacao (
    id_locacao INT NOT NULL AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_funcionario INT NOT NULL,
    id_filme INT NOT NULL,
    localizacao_filme CHAR(10) NOT NULL,
    historico_aluguel VARCHAR(50) NOT NULL,
    data_cadastro_filme DATE DEFAULT NULL,
    quantidade_copias INT NOT NULL,
    estado_filme ENUM('1','2','3','4','5') DEFAULT NULL,
    qtd_filmes_locados INT NOT NULL,
    numero_serie INT NOT NULL,
    nome_filme VARCHAR(50) NOT NULL,
    preco_aluguel DECIMAL(10,2) NOT NULL,
    alug_acres DECIMAL(10,2) NOT NULL,
    acres_lancamentos DECIMAL(10,2) NOT NULL,
    acres_estado_filme DECIMAL(10,2) NOT NULL,
    alug_preco_fixo DECIMAL(10,2) NOT NULL,
    alug_desc DECIMAL(10,2) NOT NULL,
    desc_pontos_cliente DECIMAL(10,2) NOT NULL,
    desc_feriado DECIMAL(10,2) NOT NULL,
    desc_cartao_fidelidade DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_locacao),
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_filme) REFERENCES filme(id_filme) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Verificar e criar tabela pagamento se não existir
CREATE TABLE IF NOT EXISTS pagamento (
    id_pagamento INT NOT NULL AUTO_INCREMENT,
    id_filme INT NOT NULL,
    id_locacao INT NOT NULL,
    lucro_pag DECIMAL(10,2) NOT NULL,
    alug_pag DECIMAL(10,2) NOT NULL,
    alug_juros_atrasado DECIMAL(10,2) NOT NULL,
    alug_quando_alugou DATE NOT NULL,
    alug_quem_alugou VARCHAR(50) NOT NULL,
    alug_data_devolucao DATE NOT NULL,
    alug_alteracao_monetaria DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_pagamento),
    FOREIGN KEY (id_filme) REFERENCES filme(id_filme) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_locacao) REFERENCES locacao(id_locacao) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Inserir dados de exemplo apenas se as tabelas estiverem vazias
INSERT INTO cliente (nome_cliente, cpf_cliente, idade_cliente, telefone_cliente, email_cliente, username, password, is_admin)
SELECT 'Admin', '00000000000', 30, '(41)99999-9999', 'admin@clubedafita.com', 'admin', 'admin123', 1
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE username = 'admin');

INSERT INTO cliente (nome_cliente, cpf_cliente, idade_cliente, telefone_cliente, email_cliente, username, password, is_admin)
SELECT 'Jefferson Rodrigo', '12345678910', 31, '(41)96582-3158', 'jeffer.rodr@gmail.com', 'jefferson', 'jeff123', 0
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE username = 'jefferson');

-- Inserir filmes de exemplo
INSERT INTO filme (estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
SELECT '8', 1, 'Drama', '2019-10-03', '02:02:00', 'Joaquin Phoenix, Robert De Niro', 'Coringa', 'Blu-ray', 'Todd Phillips', '+16', 'Isolado, o comediante Arthur Fleck se transforma no Coringa'
WHERE NOT EXISTS (SELECT 1 FROM filme WHERE id_filme = 1);

-- Inserir funcionário de exemplo
INSERT INTO funcionario (idade_funcionario, nome_funcionario, salario_funcionario, turno_funcionario, cargo_funcionario, sexo_funcionario, cpf_funcionario, email_funcionario)
SELECT 25, 'Maria Silva', 2500.00, 'Manhã', 'Atendente', 'Feminino', '98765432100', 'maria@clubedafita.com'
WHERE NOT EXISTS (SELECT 1 FROM funcionario WHERE cpf_funcionario = '98765432100');
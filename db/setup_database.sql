-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS video_locadora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE video_locadora;
-- Garantir que a conexão/interpretador deste script use UTF-8 completo
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

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
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    imagem VARCHAR(255) DEFAULT NULL,
    ident_midia VARCHAR(20) NOT NULL,
    ident_nome_diretor VARCHAR(50) NOT NULL,
    ident_class_indic VARCHAR(20) NOT NULL,
    ident_sinopse MEDIUMTEXT NOT NULL,
    PRIMARY KEY (id_filme)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Atualização segura para bases já existentes (adiciona coluna se ainda não existir)
ALTER TABLE filme ADD COLUMN IF NOT EXISTS imagem VARCHAR(255) NULL AFTER ident_titulo;

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
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo apenas se as tabelas estiverem vazias
INSERT INTO cliente (nome_cliente, cpf_cliente, idade_cliente, telefone_cliente, email_cliente, username, password, is_admin)
SELECT 'Admin', '00000000000', 30, '(41)99999-9999', 'admin@clubedafita.com', 'admin', 'admin123', 1
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE username = 'admin');

INSERT INTO cliente (nome_cliente, cpf_cliente, idade_cliente, telefone_cliente, email_cliente, username, password, is_admin)
SELECT 'Jefferson Rodrigo', '12345678910', 31, '(41)96582-3158', 'jeffer.rodr@gmail.com', 'jefferson', 'jeff123', 0
WHERE NOT EXISTS (SELECT 1 FROM cliente WHERE username = 'jefferson');

-- Inserir filmes de exemplo
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(1, '8', 1, 'Drama', '2019-10-03', '02:02:00', 'Joaquin Phoenix, Robert De Niro', 'Coringa', 'images/coringa.jpg', 'Blu-ray', 'Todd Phillips', '+16', 'Isolado, o comediante Arthur Fleck se transforma no Coringa');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(2, '10', 2, 'Drama', '1994-09-23', '02:22:00', 'Tim Robbins, Morgan Freeman', 'The Shawshank Redemption', 'images/the-shawshank-redemption.jpg', 'Blu-ray', 'Frank Darabont', '+16', 'Dois presos formam amizade e buscam esperança em anos de confinamento');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(3, '2', 3, 'Crime', '1972-03-24', '02:55:00', 'Marlon Brando, Al Pacino', 'The Godfather', 'images/the-godfather.jpg', 'DVD', 'Francis Ford Coppola', '+16', 'Patriarca de família mafiosa prepara a sucessão em meio a guerras de poder');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(4, '7', 4, 'Ação', '2008-07-18', '02:32:00', 'Christian Bale, Heath Ledger', 'The Dark Knight', 'images/the-dark-knight.jpg', 'Blu-ray', 'Christopher Nolan', '+14', 'Batman enfrenta o Caos instaurado pelo Coringa em Gotham');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(5, '9', 5, 'Ficção Científica', '2010-07-16', '02:28:00', 'Leonardo DiCaprio, Joseph Gordon-Levitt', 'Inception', 'images/inception.jpg', 'Blu-ray', 'Christopher Nolan', '+12', 'Um ladrão invade sonhos para plantar ideias e enfrenta seus próprios fantasmas');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(6, '5', 6, 'Crime', '1994-10-14', '02:34:00', 'John Travolta, Samuel L. Jackson', 'Pulp Fiction', 'images/pulp-fiction.jpg', 'DVD', 'Quentin Tarantino', '+16', 'Histórias entrelaçadas de criminosos e acasos violentos em Los Angeles');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(7, '8', 7, 'Drama', '1999-10-15', '02:19:00', 'Brad Pitt, Edward Norton', 'Fight Club', 'images/fight-club.jpg', 'DVD', 'David Fincher', '+18', 'Um homem encontra uma via de escape em um clube secreto de luta');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(8, '2', 8, 'Drama', '1994-07-06', '02:22:00', 'Tom Hanks, Robin Wright', 'Forrest Gump', 'images/forrest-gump.jpg', 'Blu-ray', 'Robert Zemeckis', '+12', 'Um homem simples atravessa décadas de história com coração e sorte');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(9, '8', 9, 'Ficção Científica', '1999-03-31', '02:16:00', 'Keanu Reeves, Laurence Fishburne', 'The Matrix', 'images/the-matrix.jpg', 'Blu-ray', 'Lana Wachowski & Lilly Wachowski', '+14', 'Um hacker descobre a verdade sobre a realidade e enfrenta as máquinas');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(10, '8', 10, 'Ficção Científica', '2014-11-07', '02:49:00', 'Matthew McConaughey, Anne Hathaway', 'Interstellar', 'images/interstellar.jpg', 'Blu-ray', 'Christopher Nolan', '+12', 'Exploradores atravessam um buraco de minhoca em busca de um novo lar');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(11, '5', 11, 'Drama', '2019-05-30', '02:12:00', 'Song Kang-ho, Choi Woo-shik', 'Parasite', 'images/parasite.jpg', 'Blu-ray', 'Bong Joon-ho', '+16', 'Famílias de mundos opostos se infiltram e colidem em plano arriscado');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(12, '8', 12, 'Ação', '2019-04-26', '03:01:00', 'Robert Downey Jr., Chris Evans', 'Avengers: Endgame', 'images/avengers-endgame.jpg', 'Blu-ray', 'Anthony Russo & Joe Russo', '+12', 'Heróis unem forças para reverter uma catástrofe universal');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(13, '9', 13, 'Romance', '1997-12-19', '03:14:00', 'Leonardo DiCaprio, Kate Winslet', 'Titanic', 'images/titanic.jpg', 'DVD', 'James Cameron', '+12', 'Um amor inesperado nasce a bordo de um navio destinado ao desastre');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(14, '6', 14, 'Ação', '2000-05-05', '02:35:00', 'Russell Crowe, Joaquin Phoenix', 'Gladiator', 'images/gladiator.jpg', 'DVD', 'Ridley Scott', '+16', 'Um general romano busca vingança como gladiador após traição imperial');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(15, '7', 15, 'Fantasia', '2001-12-19', '02:58:00', 'Elijah Wood, Ian McKellen', 'The Lord of the Rings: The Fellowship of the Ring', 'images/the-lord-of-the-rings-the-fellowship-of-the-ring.jpg', 'Blu-ray', 'Peter Jackson', '+12', 'Um hobbit parte para destruir um anel que corrompe tudo ao redor');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(16, '5', 16, 'Fantasia', '2002-12-18', '02:59:00', 'Elijah Wood, Viggo Mortensen', 'The Lord of the Rings: The Two Towers', 'images/the-lord-of-the-rings-the-two-towers.jpg', 'Blu-ray', 'Peter Jackson', '+12', 'A jornada continua enquanto as forças se dividem e as ameaças crescem');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(17, '9', 17, 'Fantasia', '2003-12-17', '03:21:00', 'Elijah Wood, Sean Astin', 'The Lord of the Rings: The Return of the King', 'images/the-lord-of-the-rings-the-return-of-the-king.jpg', 'Blu-ray', 'Peter Jackson', '+12', 'O confronto final decide o destino da Terra-média e do Um Anel');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(18, '2', 18, 'Ficção Científica', '1977-05-25', '02:01:00', 'Mark Hamill, Harrison Ford', 'Star Wars: A New Hope', 'images/star-wars-a-new-hope.jpg', 'DVD', 'George Lucas', '+10', 'Um fazendeiro espacial se junta à rebelião contra um império tirano');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(19, '6', 19, 'Ficção Científica', '1980-05-21', '02:04:00', 'Mark Hamill, Carrie Fisher', 'Star Wars: The Empire Strikes Back', 'images/star-wars-the-empire-strikes-back.jpg', 'DVD', 'Irvin Kershner', '+10', 'A rebelião sofre perdas enquanto segredos familiares vêm à tona');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(20, '4', 20, 'Ficção Científica', '1983-05-25', '02:11:00', 'Mark Hamill, Harrison Ford', 'Star Wars: Return of the Jedi', 'images/star-wars-return-of-the-jedi.jpg', 'DVD', 'Richard Marquand', '+10', 'A última batalha contra o império determina o destino da galáxia');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(21, '5', 21, 'Drama', '2010-10-01', '02:00:00', 'Jesse Eisenberg, Andrew Garfield', 'The Social Network', 'images/the-social-network.jpg', 'Digital', 'David Fincher', '+12', 'A criação de uma rede social gera fortuna e brigas judiciais');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(22, '6', 22, 'Drama', '2014-10-10', '01:47:00', 'Miles Teller, J.K. Simmons', 'Whiplash', 'images/whiplash.jpg', 'Blu-ray', 'Damien Chazelle', '+14', 'Um baterista ambicioso enfrenta um mentor implacável em busca da perfeição');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(23, '8', 23, 'Musical', '2016-12-09', '02:08:00', 'Ryan Gosling, Emma Stone', 'La La Land', 'images/la-la-land.jpg', 'Blu-ray', 'Damien Chazelle', '+10', 'Um músico e uma atriz lutam pelos sonhos e pelo amor em Los Angeles');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(24, '8', 24, 'Ação', '2015-05-15', '02:00:00', 'Tom Hardy, Charlize Theron', 'Mad Max: Fury Road', 'images/mad-max-fury-road.jpg', 'Blu-ray', 'George Miller', '+16', 'Em um deserto pós-apocalíptico, uma fuga desencadeia guerra selvagem');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(25, '4', 25, 'Animação', '2018-12-14', '01:57:00', 'Shameik Moore, Jake Johnson', 'Spider-Man: Into the Spider-Verse', 'images/spider-man-into-the-spider-verse.jpg', 'Blu-ray', 'Bob Persichetti, Peter Ramsey, Rodney Rothman', 'L', 'Vários Homens-Aranha colidem em uma aventura multiversal animada');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(26, '5', 26, 'Animação', '1995-11-22', '01:21:00', 'Tom Hanks, Tim Allen', 'Toy Story', 'images/toy-story.jpg', 'DVD', 'John Lasseter', 'L', 'Brinquedos ganham vida e descobrem o valor da amizade');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(27, '9', 27, 'Animação', '2003-05-30', '01:40:00', 'Albert Brooks, Ellen DeGeneres', 'Finding Nemo', 'images/finding-nemo.jpg', 'DVD', 'Andrew Stanton', 'L', 'Um pai atravessa o oceano para resgatar o filho desaparecido');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(28, '7', 28, 'Animação', '2001-07-20', '02:05:00', 'Rumi Hiiragi, Miyu Irino', 'Spirited Away', 'images/spirited-away.jpg', 'Blu-ray', 'Hayao Miyazaki', 'L', 'Uma menina entra num mundo mágico para salvar os pais enfeitiçados');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(29, '6', 29, 'Animação', '1994-06-24', '01:28:00', 'Matthew Broderick, James Earl Jones', 'The Lion King', 'images/the-lion-king.jpg', 'Blu-ray', 'Roger Allers & Rob Minkoff', 'L', 'Um jovem leão aprende seu lugar no ciclo da vida');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(30, '8', 30, 'Animação', '2009-05-29', '01:36:00', 'Ed Asner, Jordan Nagai', 'Up', 'images/up.jpg', 'DVD', 'Pete Docter & Bob Peterson', 'L', 'Um idoso e um garoto voam em casa com balões rumo a uma aventura');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(31, '5', 31, 'Animação', '2017-11-22', '01:45:00', 'Anthony Gonzalez, Gael García Bernal', 'Coco', 'images/coco.jpg', 'Blu-ray', 'Lee Unkrich & Adrian Molina', 'L', 'Um garoto visita a Terra dos Mortos para entender sua família e música');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(32, '7', 32, 'Animação', '2015-06-19', '01:35:00', 'Amy Poehler, Phyllis Smith', 'Inside Out', 'images/inside-out.jpg', 'Blu-ray', 'Pete Docter', 'L', 'As emoções de uma garota enfrentam mudanças e aprendem a cooperar');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(33, '6', 33, 'Animação', '2004-11-05', '01:55:00', 'Craig T. Nelson, Holly Hunter', 'The Incredibles', 'images/the-incredibles.jpg', 'DVD', 'Brad Bird', 'L', 'Uma família de super-heróis tenta viver anonimamente até a próxima missão');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(34, '3', 34, 'Animação', '2008-06-27', '01:38:00', 'Ben Burtt, Elissa Knight', 'WALL·E', 'images/wall-e.jpg', 'Blu-ray', 'Andrew Stanton', 'L', 'Um robô compactador encontra amor e esperança no espaço');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(35, '6', 35, 'Suspense', '1991-02-14', '01:58:00', 'Jodie Foster, Anthony Hopkins', 'The Silence of the Lambs', 'images/the-silence-of-the-lambs.jpg', 'Blu-ray', 'Jonathan Demme', '+16', 'Uma agente busca ajuda de um assassino preso para capturar outro criminoso');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(36, '8', 36, 'Crime', '1995-09-22', '02:07:00', 'Brad Pitt, Morgan Freeman', 'Se7en', 'images/se7en.jpg', 'DVD', 'David Fincher', '+16', 'Dois detetives caçam um serial killer obcecado pelos pecados capitais');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(37, '8', 37, 'Suspense', '1995-08-16', '01:46:00', 'Kevin Spacey, Gabriel Byrne', 'The Usual Suspects', 'images/the-usual-suspects.jpg', 'DVD', 'Bryan Singer', '+16', 'Criminosos reunidos em um golpe descobrem que nada é o que parece');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(38, '8', 38, 'Crime', '1990-09-19', '02:26:00', 'Ray Liotta, Robert De Niro', 'Goodfellas', 'images/goodfellas.jpg', 'Blu-ray', 'Martin Scorsese', '+16', 'Ascensão e queda de um mafioso ao longo de décadas');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(39, '7', 39, 'Crime', '2006-10-06', '02:31:00', 'Leonardo DiCaprio, Matt Damon', 'The Departed', 'images/the-departed.jpg', 'Blu-ray', 'Martin Scorsese', '+16', 'Polícia e máfia se infiltram uma na outra em um jogo mortal');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(40, '8', 40, 'Suspense', '2006-10-20', '02:10:00', 'Christian Bale, Hugh Jackman', 'The Prestige', 'images/the-prestige.jpg', 'DVD', 'Christopher Nolan', '+12', 'Rivais ilusionistas travam disputa que beira a obsessão');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(41, '9', 41, 'Suspense', '2000-10-11', '01:53:00', 'Guy Pearce, Carrie-Anne Moss', 'Memento', 'images/memento.jpg', 'DVD', 'Christopher Nolan', '+14', 'Um homem com amnésia recente busca o assassino da esposa usando pistas');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(42, '10', 42, 'Drama', '1999-12-10', '03:09:00', 'Tom Hanks, Michael Clarke Duncan', 'The Green Mile', 'images/the-green-mile.jpg', 'Blu-ray', 'Frank Darabont', '+14', 'Guardas presenciam eventos misteriosos no corredor da morte');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(43, '7', 43, 'Guerra', '1998-07-24', '02:49:00', 'Tom Hanks, Matt Damon', 'Saving Private Ryan', 'images/saving-private-ryan.jpg', 'Blu-ray', 'Steven Spielberg', '+16', 'Um pelotão cruza a França ocupada para resgatar um único soldado');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(44, '3', 44, 'Drama', '1993-12-15', '03:15:00', 'Liam Neeson, Ben Kingsley', 'Schindler''s List', 'images/schindlers-list.jpg', 'DVD', 'Steven Spielberg', '+14', 'Um industrial salva judeus durante o Holocausto à custa de tudo que tem');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(45, '6', 45, 'Guerra', '1995-05-24', '02:58:00', 'Mel Gibson, Sophie Marceau', 'Braveheart', 'images/braveheart.jpg', 'DVD', 'Mel Gibson', '+16', 'Um guerreiro escocês lidera a luta por liberdade contra a opressão');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(46, '5', 46, 'Guerra', '2017-07-21', '01:46:00', 'Fionn Whitehead, Harry Styles', 'Dunkirk', 'images/dunkirk.jpg', 'Blu-ray', 'Christopher Nolan', '+12', 'Soldados cercados buscam evacuação em três frentes durante a guerra');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(47, '1', 47, 'Guerra', '2019-12-25', '01:59:00', 'George MacKay, Dean-Charles Chapman', '1917', 'images/1917.jpg', 'Blu-ray', 'Sam Mendes', '+14', 'Dois soldados correm contra o tempo para entregar uma mensagem vital');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(48, '8', 48, 'Drama', '2015-12-25', '02:36:00', 'Leonardo DiCaprio, Tom Hardy', 'The Revenant', 'images/the-revenant.jpg', 'Blu-ray', 'Alejandro G. Iñárritu', '+16', 'Após ser deixado para morrer, um explorador busca sobreviver e se vingar');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(49, '7', 49, 'Crime', '2007-11-21', '02:02:00', 'Tommy Lee Jones, Javier Bardem', 'No Country for Old Men', 'images/no-country-for-old-men.jpg', 'DVD', 'Joel Coen & Ethan Coen', '+16', 'Um caçador encontra dinheiro e é perseguido por um assassino implacável');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(50, '4', 50, 'Comédia', '2014-03-28', '01:39:00', 'Ralph Fiennes, Tony Revolori', 'The Grand Budapest Hotel', 'images/the-grand-budapest-hotel.jpg', 'Blu-ray', 'Wes Anderson', '+12', 'Um concierge e um mensageiro se envolvem em herança e aventura');
INSERT INTO video_locadora.filme
(id_filme, estado_filme, identificacao_filme, ident_genero, ident_data, ident_duracao, ident_elenco, ident_titulo, imagem, ident_midia, ident_nome_diretor, ident_class_indic, ident_sinopse)
VALUES(51, '5', 51, 'Romance', '2013-11-20', '02:06:00', 'Joaquin Phoenix, Scarlett Johansson', 'Her', 'images/her.jpg', 'Digital', 'Spike Jonze', '+14', 'Um homem solitário se apaixona por um sistema operacional inteligente');

-- Inserir funcionário de exemplo
INSERT INTO funcionario (idade_funcionario, nome_funcionario, salario_funcionario, turno_funcionario, cargo_funcionario, sexo_funcionario, cpf_funcionario, email_funcionario)
SELECT 25, 'Maria Silva', 2500.00, 'Manhã', 'Atendente', 'Feminino', '98765432100', 'maria@clubedafita.com'
WHERE NOT EXISTS (SELECT 1 FROM funcionario WHERE cpf_funcionario = '98765432100');

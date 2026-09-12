-- Criação de uma tabela separada de categorias para se comportar como um ENUM
CREATE TABLE IF NOT EXISTS categoria (
    categoria_id INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Evita erros de encoding

-- Criação da tabela livros 
CREATE TABLE IF NOT EXISTS livros (
    livro_id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    autor VARCHAR(50) NOT NULL,
    categoria_id INT,
    status BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_livros_categoria
        FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserção de valores padrão para as categorias possiveis para o livro
SET NAMES utf8mb4;
INSERT INTO categoria (nome_categoria) VALUES
    ('Ficção Científica'),
    ('Fantasia'),
    ('Romance'),
    ('Suspense e Mistério'),
    ('Terror e Horror'),
    ('Ficção Policial'),
    ('Aventura'),
    ('Ficção Histórica'),
    ('Biografia e Autobiografia'),
    ('História'),
    ('Filosofia'),
    ('Psicologia'),
    ('Autoajuda e Desenvolvimento Pessoal'),
    ('Negócios e Economia'),
    ('Tecnologia e Informática'),
    ('Poesia'),
    ('Drama'),
    ('Infantojuvenil'),
    ('Quadrinhos e HQs'),
    ('Religião e Espiritualidade'),
    ('Ciências'),
    ('Educação e Didáticos');


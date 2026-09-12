<?php

// Models são responsáveis por funções que serão utilizadas pelos Controllers para fazer as buscas dos dados no banco de dados
// Utilizei o PDO para fazer as queries e conexão com o banco
// Dessa forma consigo modularizar melhor o sistema para condensar as queries em funções que serão reutilizadas depois

namespace App\Models;

use App\Config\Database;
use PDO;

class Livro
{
    /**
     * Busca livros com suporte a filtros
     *
     * @param array $filters Filtros suportados: id, categoria, categoria_id, autor, titulo, status
     * @return array
     */
    public static function findAll(array $filters = []): array
    {
        $pdo = Database::getConnection();

        $sql = 'SELECT 
                    l.livro_id,
                    l.titulo,
                    l.autor,
                    l.categoria_id,
                    c.nome_categoria,
                    l.status
                FROM livros l
                LEFT JOIN categoria c ON l.categoria_id = c.categoria_id
                WHERE 1=1';

        $params = [];

        // Filtro por ID
        if (!empty($filters['id'])) {
            $sql .= ' AND l.livro_id = :id';
            $params['id'] = (int) $filters['id'];
        }

        // Filtro por categoria (aceita ID numérico ou nome parcial/exato da categoria)
        if (!empty($filters['categoria'])) {
            $categoria = trim((string) $filters['categoria']);
            if (is_numeric($categoria)) {
                $sql .= ' AND l.categoria_id = :categoria_num';
                $params['categoria_num'] = (int) $categoria;
            } else {
                $sql .= ' AND c.nome_categoria LIKE :categoria_nome';
                $params['categoria_nome'] = '%' . $categoria . '%';
            }
        }

        // Filtro explícito por categoria_id
        if (!empty($filters['categoria_id'])) {
            $sql .= ' AND l.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }

        // Filtro por autor (busca parcial com LIKE)
        if (!empty($filters['autor'])) {
            $sql .= ' AND l.autor LIKE :autor';
            $params['autor'] = '%' . trim((string) $filters['autor']) . '%';
        }

        // Filtro por título (busca parcial com LIKE)
        if (!empty($filters['titulo'])) {
            $sql .= ' AND l.titulo LIKE :titulo';
            $params['titulo'] = '%' . trim((string) $filters['titulo']) . '%';
        }

        // Filtro por status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= ' AND l.status = :status';
            $params['status'] = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }

        $sql .= ' ORDER BY l.livro_id ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map([self::class, 'formatRow'], $rows);
    }


    /**
     * Busca todos os livros ativos (status = true)
     *
     * @return array
     */
    public static function findByStatus(): array
    {
        $pdo = Database::getConnection();

        $sql = 'SELECT
                    l.livro_id,
                    l.titulo,
                    l.autor,
                    l.categoria_id,
                    c.nome_categoria,
                    l.status
                FROM livros l
                LEFT JOIN categoria c ON l.categoria_id = c.categoria_id
                WHERE l.status = 1
                ORDER BY l.livro_id ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map([self::class, 'formatRow'], $rows);
    }


    /**
     * Busca um livro pelo seu ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $sql = 'SELECT 
                    l.livro_id,
                    l.titulo,
                    l.autor,
                    l.categoria_id,
                    c.nome_categoria,
                    l.status
                FROM livros l
                LEFT JOIN categoria c ON l.categoria_id = c.categoria_id
                WHERE l.livro_id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? self::formatRow($row) : null;
    }

    /**
     * Cria um novo livro no banco de dados
     */
    public static function create(array $data): ?array
    {
        $pdo = Database::getConnection();

        $sql = 'INSERT INTO livros (titulo, autor, categoria_id, status) 
                VALUES (:titulo, :autor, :categoria_id, :status)';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'titulo' => trim((string) $data['titulo']),
            'autor' => trim((string) $data['autor']),
            'categoria_id' => (int) $data['categoria_id'],
            'status' => isset($data['status']) ? (filter_var($data['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : 1,
        ]);

        $newId = (int) $pdo->lastInsertId();
        return self::findById($newId);
    }

    /**
     * Atualiza um livro existente
     */
    public static function update(int $id, array $data): ?array
    {
        $pdo = Database::getConnection();

        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('titulo', $data)) {
            $fields[] = 'titulo = :titulo';
            $params['titulo'] = trim((string) $data['titulo']);
        }

        if (array_key_exists('autor', $data)) {
            $fields[] = 'autor = :autor';
            $params['autor'] = trim((string) $data['autor']);
        }

        if (array_key_exists('categoria_id', $data)) {
            $fields[] = 'categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $data['categoria_id'];
        }

        if (array_key_exists('status', $data)) {
            $fields[] = 'status = :status';
            $params['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }

        if (empty($fields)) {
            return self::findById($id);
        }

        $sql = 'UPDATE livros SET ' . implode(', ', $fields) . ' WHERE livro_id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::findById($id);
    }

    /**
     * Verifica se já existe um livro com o mesmo título e autor
     *
     * @param string $titulo
     * @param string $autor
     * @param int|null $excludeId ID a ser excluído da verificação (útil no update)
     * @return bool
     */
    public static function existsByTituloAndAutor(string $titulo, string $autor, ?int $excludeId = null): bool
    {
        $pdo = Database::getConnection();

        $sql = 'SELECT COUNT(*) FROM livros WHERE LOWER(titulo) = LOWER(:titulo) AND LOWER(autor) = LOWER(:autor)';
        $params = [
            'titulo' => trim($titulo),
            'autor'  => trim($autor),
        ];

        if ($excludeId !== null) {
            $sql .= ' AND livro_id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Remove um livro pelo ID
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM livros WHERE livro_id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Formata os tipos dos campos retornados do banco de dados
     */
    private static function formatRow(array $row): array
    {
        return [
            'livro_id' => (int) $row['livro_id'],
            'titulo' => $row['titulo'],
            'autor' => $row['autor'],
            'status' => (bool) $row['status'],
            'categoria_id' => $row['categoria_id'] !== null ? (int) $row['categoria_id'] : null,
            'categoria' => $row['categoria_id'] !== null ? [
                'categoria_id' => (int) $row['categoria_id'],
                'nome_categoria' => $row['nome_categoria'],
            ] : null,
        ];
    }
}

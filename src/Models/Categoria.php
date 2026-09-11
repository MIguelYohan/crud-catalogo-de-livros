<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Categoria
{
    /**
     * Retorna todas as categorias cadastradas
     */
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT categoria_id, nome_categoria FROM categoria ORDER BY nome_categoria ASC');
        return $stmt->fetchAll();
    }

    /**
     * Busca uma categoria pelo ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT categoria_id, nome_categoria FROM categoria WHERE categoria_id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verifica se existe uma categoria com o ID informado
     */
    public static function exists(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM categoria WHERE categoria_id = :id');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}

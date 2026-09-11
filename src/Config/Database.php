<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    /**
     * Retorna a instância única de conexão PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = Env::get('DB_HOST', Env::get('MYSQL_HOST', 'mysql'));
            $port = Env::get('DB_PORT', Env::get('MYSQL_PORT', '3306'));
            $dbname = Env::get('DB_DATABASE', Env::get('MYSQL_DATABASE', 'catalogo_livros'));
            $user = Env::get('DB_USER', Env::get('MYSQL_USER', 'catalogo_user'));
            $pass = Env::get('DB_PASSWORD', Env::get('MYSQL_PASSWORD', 'catalogopassword'));
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Tenta fallback para 127.0.0.1 caso o host informado tenha sido 'mysql' mas estejamos rodando fora do container
                if ($host === 'mysql') {
                    $fallbackHost = '127.0.0.1';
                    $fallbackPort = Env::get('MYSQL_PORT', '3307');
                    $fallbackDsn = "mysql:host={$fallbackHost};port={$fallbackPort};dbname={$dbname};charset={$charset}";
                    try {
                        self::$connection = new PDO($fallbackDsn, $user, $pass, $options);
                        return self::$connection;
                    } catch (PDOException) {
                        // Se também falhar, lança o erro original
                    }
                }

                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => 'Erro ao conectar com o banco de dados MySQL: ' . $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$connection;
    }
}

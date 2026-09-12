<?php

// Controllers são responsáveis por utilizar os dados extraidos pelos models e retorna-los em formato JSON

namespace App\Controllers;

use App\Models\Categoria;

class CategoriaController
{
    /**
     * GET /categorias
     * Lista todas as categorias
     */
    public function index(): void
    {
        $categorias = Categoria::all();

        $this->json([
            'status' => 'sucesso',
            'total' => count($categorias),
            'dados' => $categorias,
        ], 200);
    }

    /**
     * GET /categorias/{id}
     * Exibe os detalhes de uma categoria específica
     */
    public function show(int $id): void
    {
        $categoria = Categoria::findById($id);

        if (!$categoria) {
            $this->json([
                'status' => 'erro',
                'mensagem' => 'Categoria não encontrada.',
            ], 404);
            return;
        }

        $this->json([
            'status' => 'sucesso',
            'dados' => $categoria,
        ], 200);
    }

    /**
     * Envia uma resposta JSON padronizada
     */
    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

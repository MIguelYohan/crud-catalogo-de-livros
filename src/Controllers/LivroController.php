<?php

namespace App\Controllers;

use App\Models\Livro;
use App\Validators\LivroValidator;

class LivroController
{
    /**
     * GET /livros
     * Lista livros com suporte a filtros via query params:
     * - id
     * - categoria
     * - categoria_id
     * - autor
     * - titulo
     * - status
     */
    public function index(array $queryParams = []): void
    {
        $livros = Livro::findAll($queryParams);

        $this->json([
            'status' => 'sucesso',
            'total'  => count($livros),
            'dados'  => $livros,
        ], 200);
    }

    /**
     * GET /livros/ativos
     * Lista apenas os livros com status ativo (true)
     */
    public function active(): void
    {
        $livros = Livro::findByStatus();

        $this->json([
            'status' => 'sucesso',
            'total'  => count($livros),
            'dados'  => $livros,
        ], 200);
    }

    /**
     * GET /livros/{id}
     * Exibe os detalhes de um livro específico
     */
    public function show(int $id): void
    {
        $livro = Livro::findById($id);

        if (!$livro) {
            $this->json([
                'status' => 'erro',
                'mensagem' => 'Livro não encontrado.',
            ], 404);
            return;
        }

        $this->json([
            'status' => 'sucesso',
            'dados' => $livro,
        ], 200);
    }

    /**
     * POST /livros
     * Cria um novo livro após validação dos dados
     */
    public function store(array $body): void
    {
        $errors = LivroValidator::validateCreate($body);

        if (!empty($errors)) {
            $this->json([
                'status' => 'erro',
                'mensagem' => 'Falha na validação dos dados.',
                'erros' => $errors,
            ], 422);
            return;
        }

        $novoLivro = Livro::create($body);

        $this->json([
            'status' => 'sucesso',
            'mensagem' => 'Livro cadastrado com sucesso!',
            'dados' => $novoLivro,
        ], 201);
    }

    /**
     * PUT /livros/{id}
     * Atualiza um livro existente após validação
     */
    public function update(int $id, array $body): void
    {
        $livroExistente = Livro::findById($id);
        if (!$livroExistente) {
            $this->json([
                'status' => 'erro',
                'mensagem' => 'Livro não encontrado para atualização.',
            ], 404);
            return;
        }

        $errors = LivroValidator::validateUpdate($body, $id);

        if (!empty($errors)) {
            $this->json([
                'status' => 'erro',
                'mensagem' => 'Falha na validação dos dados.',
                'erros' => $errors,
            ], 422);
            return;
        }

        $livroAtualizado = Livro::update($id, $body);

        $this->json([
            'status' => 'sucesso',
            'mensagem' => 'Livro atualizado com sucesso!',
            'dados' => $livroAtualizado,
        ], 200);
    }

    /**
     * DELETE /livros/{id}
     * Remove um livro do catálogo
     */
    public function destroy(int $id): void
    {
        $livroExistente = Livro::findById($id);
        if (!$livroExistente) {
            $this->json([
                'status' => 'erro',
                'mensagem' => 'Livro não encontrado para exclusão.',
            ], 404);
            return;
        }

        Livro::delete($id);

        $this->json([
            'status' => 'sucesso',
            'mensagem' => 'Livro excluído com sucesso.',
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

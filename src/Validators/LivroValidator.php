<?php

namespace App\Validators;

use App\Models\Categoria;

class LivroValidator
{
    /**
     * Valida os dados para criação de um livro (POST)
     *
     * @param array $data
     * @return array Lista de erros (vazio se válido)
     */
    public static function validateCreate(array $data): array
    {
        $errors = [];

        // Validação do Título
        if (!isset($data['titulo']) || trim((string) $data['titulo']) === '') {
            $errors['titulo'][] = 'O campo título é obrigatório.';
        } elseif (!is_string($data['titulo'])) {
            $errors['titulo'][] = 'O campo título deve ser um texto.';
        } elseif (mb_strlen(trim($data['titulo'])) > 100) {
            $errors['titulo'][] = 'O campo título não pode ultrapassar 100 caracteres.';
        }

        // Validação do Autor
        if (!isset($data['autor']) || trim((string) $data['autor']) === '') {
            $errors['autor'][] = 'O campo autor é obrigatório.';
        } elseif (!is_string($data['autor'])) {
            $errors['autor'][] = 'O campo autor deve ser um texto.';
        } elseif (mb_strlen(trim($data['autor'])) > 50) {
            $errors['autor'][] = 'O campo autor não pode ultrapassar 50 caracteres.';
        }

        // Validação da Categoria
        if (!isset($data['categoria_id']) || $data['categoria_id'] === '') {
            $errors['categoria_id'][] = 'O campo categoria_id é obrigatório.';
        } elseif (!filter_var($data['categoria_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $errors['categoria_id'][] = 'O campo categoria_id deve ser um número inteiro positivo válido.';
        } elseif (!Categoria::exists((int) $data['categoria_id'])) {
            $errors['categoria_id'][] = 'A categoria informada não existe.';
        }

        // Validação do Status (opcional)
        if (isset($data['status'])) {
            $statusVal = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($statusVal === null) {
                $errors['status'][] = 'O campo status deve ser um valor booleano (true ou false).';
            }
        }

        return $errors;
    }

    /**
     * Valida os dados para atualização de um livro (PUT)
     *
     * @param array $data
     * @return array Lista de erros (vazio se válido)
     */
    public static function validateUpdate(array $data): array
    {
        $errors = [];

        if (empty($data)) {
            $errors['geral'][] = 'Informe pelo menos um campo para atualização.';
            return $errors;
        }

        // Validação do Título se presente
        if (array_key_exists('titulo', $data)) {
            if (trim((string) $data['titulo']) === '') {
                $errors['titulo'][] = 'O campo título não pode ser vazio.';
            } elseif (!is_string($data['titulo'])) {
                $errors['titulo'][] = 'O campo título deve ser um texto.';
            } elseif (mb_strlen(trim($data['titulo'])) > 100) {
                $errors['titulo'][] = 'O campo título não pode ultrapassar 100 caracteres.';
            }
        }

        // Validação do Autor se presente
        if (array_key_exists('autor', $data)) {
            if (trim((string) $data['autor']) === '') {
                $errors['autor'][] = 'O campo autor não pode ser vazio.';
            } elseif (!is_string($data['autor'])) {
                $errors['autor'][] = 'O campo autor deve ser um texto.';
            } elseif (mb_strlen(trim($data['autor'])) > 50) {
                $errors['autor'][] = 'O campo autor não pode ultrapassar 50 caracteres.';
            }
        }

        // Validação da Categoria se presente
        if (array_key_exists('categoria_id', $data)) {
            if ($data['categoria_id'] === '' || $data['categoria_id'] === null) {
                $errors['categoria_id'][] = 'O campo categoria_id não pode ser vazio.';
            } elseif (!filter_var($data['categoria_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                $errors['categoria_id'][] = 'O campo categoria_id deve ser um número inteiro positivo válido.';
            } elseif (!Categoria::exists((int) $data['categoria_id'])) {
                $errors['categoria_id'][] = 'A categoria informada não existe.';
            }
        }

        // Validação do Status se presente
        if (array_key_exists('status', $data)) {
            $statusVal = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($statusVal === null) {
                $errors['status'][] = 'O campo status deve ser um valor booleano (true ou false).';
            }
        }

        return $errors;
    }
}

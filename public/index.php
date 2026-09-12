<?php

// index.php implementa os endpoints da API controlando as requisições feitas

require_once __DIR__ . '/../src/autoload.php';

use App\Config\Env;
use App\Controllers\CategoriaController;
use App\Controllers\LivroController;
use App\Routes\Router;

// Carrega variáveis do .env
Env::load(__DIR__ . '/../.env');

$router = new Router();

// Rota raiz com documentação da API
$router->get('/', function () {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'nome'      => 'API Catálogo de Livros',
        'versao'    => '1.0.0',
        'linguagem' => 'PHP Puro',
        'endpoints' => [
            'GET /livros'            => 'Lista todos os livros (suporta filtros: ?id=, ?categoria=, ?autor=, ?titulo=, ?status=)',
            'GET /livros?id=1'       => 'Retorna os detalhes de um livro específico',
            'GET /livros/ativos'     => 'Lista apenas livros com status ativo',
            'POST /livros'           => 'Cadastra um novo livro (validação obrigatória no servidor)',
            'PUT /livros?id=1'       => 'Atualiza os dados de um livro existente',
            'DELETE /livros?id=1'    => 'Exclui um livro do catálogo',
            'GET /categorias'        => 'Lista todas as categorias disponíveis',
            'GET /categorias?id=1'   => 'Retorna detalhes de uma categoria específica',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
});

// CRUD de Livros
$router->get('/livros/ativos', function () {
    $controller = new LivroController();
    $controller->active();
});

$router->get('/livros', function () {
    $controller = new LivroController();
    $controller->index($_GET);
});

$router->post('/livros', function () {
    $rawBody = file_get_contents('php://input');
    $body = [];
    if (!empty($rawBody)) {
        $decoded = json_decode($rawBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $body = $decoded;
        } else {
            $body = $_POST;
        }
    } elseif (!empty($_POST)) {
        $body = $_POST;
    }

    $controller = new LivroController();
    $controller->store($body);
});

$router->put('/livros', function () {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    $rawBody = file_get_contents('php://input');
    $body = [];
    if (!empty($rawBody)) {
        $decoded = json_decode($rawBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $body = $decoded;
        } else {
            $body = $_POST;
        }
    } elseif (!empty($_POST)) {
        $body = $_POST;
    }

    $controller = new LivroController();
    $controller->update($id, $body);
});

$router->delete('/livros', function () {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    $controller = new LivroController();
    $controller->destroy($id);
});

// Categorias
$router->get('/categorias', function () {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    $controller = new CategoriaController();

    if ($id !== null) {
        $controller->show($id);
    } else {
        $controller->index();
    }
});

// Despacha a requisição
$router->dispatch();

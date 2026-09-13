# Catálogo de Livros

Sistema com API (PHP) e Frontend estático, usando MySQL como banco de dados.

## Como executar

### Clonar o repositório
```bash
git clone https://github.com/MIguelYohan/crud-catalogo-de-livros
cd crud-catalogo-de-livros
```

### Pré-requisitos
- Docker
- Docker Compose

### Configuração
1. Copie o arquivo de exemplo de variáveis de ambiente:
```bash
cp .env.example .env
```

2. Ajuste as variáveis no `.env` se necessário (senhas, portas, etc.)

### Subir os containers
```bash
docker-compose up -d
```

### Acessar
- **Frontend**: http://localhost:8080
- **API**: http://localhost:8000
- **MySQL**: porta 3306 (conforme `.env`)

### Parar
```bash
docker-compose down
```

### Ver logs
```bash
docker-compose logs -f
```
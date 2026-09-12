const API_BASE_URL = "http://localhost:8000";

const form = document.getElementById("livro-form");
const tituloInput = document.getElementById("titulo");
const autorInput = document.getElementById("autor");
const categoriaSelect = document.getElementById("categoria");
const statusSelect = document.getElementById("status");
const livroIdInput = document.getElementById("livro-id");
const btnSalvar = document.getElementById("btn-salvar");
const btnCancelar = document.getElementById("btn-cancelar");
const tbody = document.getElementById("tbody-livros");
const mensagemVazia = document.getElementById("mensagem-vazia");
const feedback = document.getElementById("feedback");
const buscaInput = document.getElementById("busca");
const btnBuscar = document.getElementById("btn-buscar");
const btnListarTodos = document.getElementById("btn-listar-todos");
const btnAtivos = document.getElementById("btn-ativos");

let editandoId = null;

function mostrarFeedback(mensagem, tipo) {
    feedback.textContent = mensagem;
    feedback.className = "feedback " + tipo;
    setTimeout(() => {
        feedback.className = "feedback";
        feedback.textContent = "";
    }, 4000);
}

function mostrarErroCampo(campo, mensagem) {
    const erroEl = document.getElementById("erro-" + campo);
    const inputEl = document.getElementById(campo);
    if (erroEl) erroEl.textContent = mensagem;
    if (inputEl) inputEl.classList.add("error");
}

function limparErros() {
    document.querySelectorAll(".error-message").forEach(el => el.textContent = "");
    document.querySelectorAll(".form-group input, .form-group select").forEach(el => el.classList.remove("error"));
}

function validarFormulario() {
    limparErros();
    let valido = true;

    if (!tituloInput.value.trim()) {
        mostrarErroCampo("titulo", "O título é obrigatório");
        valido = false;
    }

    if (!autorInput.value.trim()) {
        mostrarErroCampo("autor", "O autor é obrigatório");
        valido = false;
    }

    if (!categoriaSelect.value) {
        mostrarErroCampo("categoria", "Selecione uma categoria");
        valido = false;
    }

    return valido;
}

function resetarFormulario() {
    form.reset();
    livroIdInput.value = "";
    editandoId = null;
    btnSalvar.textContent = "Salvar";
    btnCancelar.style.display = "none";
    limparErros();
}

function preencherFormulario(livro) {
    const id = livro.livro_id;
    const categoriaId = livro.categoria?.categoria_id;
    livroIdInput.value = id;
    tituloInput.value = livro.titulo;
    autorInput.value = livro.autor;
    categoriaSelect.value = categoriaId;
    statusSelect.value = livro.status ? "true" : "false";
    editandoId = id;
    btnSalvar.textContent = "Atualizar";
    btnCancelar.style.display = "inline-block";
    window.scrollTo({ top: 0, behavior: "smooth" });
}

async function carregarCategorias() {
    try {
        const response = await fetch(API_BASE_URL + "/categorias");
        if (!response.ok) {
            const erro = await response.json();
            throw new Error(erro.mensagem || "Erro ao carregar categorias");
        }
        const result = await response.json();
        const categorias = result.dados || [];
        categoriaSelect.innerHTML = '<option value="">Selecione uma categoria</option>';
        categorias.forEach(cat => {
            const option = document.createElement("option");
            option.value = cat.categoria_id;
            option.textContent = cat.nome_categoria;
            categoriaSelect.appendChild(option);
        });
    } catch (error) {
        console.error("Erro ao carregar categorias:", error);
        mostrarFeedback("Erro ao carregar categorias: " + error.message, "error");
    }
}

function criarLinhaTabela(livro) {
    const id = livro.livro_id;
    const categoriaNome = livro.categoria?.nome_categoria ?? '';
    const tr = document.createElement("tr");
    tr.innerHTML = `
        <td data-label="ID">${id}</td>
        <td data-label="Título">${livro.titulo}</td>
        <td data-label="Autor">${livro.autor}</td>
        <td data-label="Categoria">${categoriaNome}</td>
        <td data-label="Status">
            <span class="status-badge ${livro.status ? 'ativo' : 'inativo'}">
                ${livro.status ? 'Ativo' : 'Inativo'}
            </span>
        </td>
        <td data-label="Ações">
            <div class="actions">
                <button class="btn btn-edit" onclick="editarLivro(${id})">Editar</button>
                <button class="btn btn-danger" onclick="excluirLivro(${id})">Excluir</button>
            </div>
        </td>
    `;
    return tr;
}

async function carregarLivros(url) {
    tbody.innerHTML = '<tr><td colspan="6" class="loading">Carregando livros...</td></tr>';
    mensagemVazia.style.display = "none";

    try {
        const response = await fetch(url);
        if (!response.ok) {
            const erro = await response.json();
            throw new Error(erro.mensagem || "Erro ao buscar livros");
        }
        const result = await response.json();
        const livros = result.dados || [];

        tbody.innerHTML = "";
        if (livros.length === 0) {
            mensagemVazia.style.display = "block";
            return;
        }

        livros.forEach(livro => {
            tbody.appendChild(criarLinhaTabela(livro));
        });
    } catch (error) {
        console.error("Erro ao carregar livros:", error);
        tbody.innerHTML = '<tr><td colspan="6" class="loading" style="color: #e74c3c;">Erro ao carregar livros: ' + error.message + '</td></tr>';
        mostrarFeedback("Erro ao carregar livros: " + error.message, "error");
    }
}

async function salvarLivro(event) {
    event.preventDefault();

    if (!validarFormulario()) return;

    const livro = {
        titulo: tituloInput.value.trim(),
        autor: autorInput.value.trim(),
        categoria_id: parseInt(categoriaSelect.value),
        status: statusSelect.value === "true"
    };

    const url = editandoId
        ? API_BASE_URL + "/livros?id=" + editandoId
        : API_BASE_URL + "/livros";

    const method = editandoId ? "PUT" : "POST";

    try {
        const response = await fetch(url, {
            method: method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(livro)
        });

        if (!response.ok) {
            const erro = await response.json();
            throw new Error(erro.mensagem || "Erro ao salvar livro");
        }

        mostrarFeedback(editandoId ? "Livro atualizado com sucesso!" : "Livro cadastrado com sucesso!", "success");
        resetarFormulario();
        carregarLivros(API_BASE_URL + "/livros");
    } catch (error) {
        console.error("Erro ao salvar livro:", error);
        mostrarFeedback("Erro ao salvar livro: " + error.message, "error");
    }
}

async function editarLivro(id) {
    try {
        const response = await fetch(API_BASE_URL + "/livros?id=" + id);
        if (!response.ok) {
            const erro = await response.json();
            throw new Error(erro.mensagem || "Erro ao buscar livro");
        }
        const result = await response.json();
        const livros = result.dados || [];
        const livro = livros[0];
        if (!livro) throw new Error("Livro não encontrado");
        preencherFormulario(livro);
    } catch (error) {
        console.error("Erro ao buscar livro para edição:", error);
        mostrarFeedback("Erro ao carregar livro: " + error.message, "error");
    }
}

async function excluirLivro(id) {
    if (!confirm("Tem certeza que deseja excluir este livro?")) return;

    try {
        const response = await fetch(API_BASE_URL + "/livros?id=" + id, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" }
        });

        if (!response.ok) {
            const erro = await response.json();
            throw new Error(erro.mensagem || "Erro ao excluir livro");
        }

        mostrarFeedback("Livro excluído com sucesso!", "success");
        carregarLivros(API_BASE_URL + "/livros");
    } catch (error) {
        console.error("Erro ao excluir livro:", error);
        mostrarFeedback("Erro ao excluir livro: " + error.message, "error");
    }
}

async function buscarLivros() {
    const termo = buscaInput.value.trim();
    if (!termo) {
        carregarLivros(API_BASE_URL + "/livros");
        return;
    }

    try {
        const urlTitulo = API_BASE_URL + "/livros?titulo=" + encodeURIComponent(termo);
        const responseTitulo = await fetch(urlTitulo);
        if (!responseTitulo.ok) {
            const erro = await responseTitulo.json();
            throw new Error(erro.mensagem || "Erro ao buscar livros por título");
        }
        const resultTitulo = await responseTitulo.json();
        const livrosTitulo = resultTitulo.dados || [];

        if (livrosTitulo.length > 0) {
            tbody.innerHTML = "";
            livrosTitulo.forEach(livro => {
                tbody.appendChild(criarLinhaTabela(livro));
            });
            mensagemVazia.style.display = "none";
            return;
        }

        const urlAutor = API_BASE_URL + "/livros?autor=" + encodeURIComponent(termo);
        const responseAutor = await fetch(urlAutor);
        if (!responseAutor.ok) {
            const erro = await responseAutor.json();
            throw new Error(erro.mensagem || "Erro ao buscar livros por autor");
        }
        const resultAutor = await responseAutor.json();
        const livrosAutor = resultAutor.dados || [];

        tbody.innerHTML = "";
        if (livrosAutor.length === 0) {
            mensagemVazia.style.display = "block";
        } else {
            mensagemVazia.style.display = "none";
            livrosAutor.forEach(livro => {
                tbody.appendChild(criarLinhaTabela(livro));
            });
        }
    } catch (error) {
        console.error("Erro ao buscar livros:", error);
        tbody.innerHTML = '<tr><td colspan="6" class="loading" style="color: #e74c3c;">Erro ao buscar livros: ' + error.message + '</td></tr>';
        mostrarFeedback("Erro ao buscar livros: " + error.message, "error");
    }
}

function inicializarEventos() {
    form.addEventListener("submit", salvarLivro);
    btnCancelar.addEventListener("click", resetarFormulario);
    btnBuscar.addEventListener("click", buscarLivros);
    btnListarTodos.addEventListener("click", () => {
        buscaInput.value = "";
        carregarLivros(API_BASE_URL + "/livros");
    });
    btnAtivos.addEventListener("click", () => {
        buscaInput.value = "";
        carregarLivros(API_BASE_URL + "/livros/ativos");
    });
    buscaInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") buscarLivros();
    });
}

async function init() {
    await carregarCategorias();
    inicializarEventos();
    carregarLivros(API_BASE_URL + "/livros");
}

init();
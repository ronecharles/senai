<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Exemplo: Digitação Automática + Fetch</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Buscar Usuários via Formulário + Fetch</h1>

    <form id="form-consulta">
      <label for="emailId">Email (filtro):</label>
      <input
        type="email"
        id="emailId"
        name="emailId"
        placeholder="Digite um email"
        required
      />
      <button type="submit">Consultar</button>
    </form>

    <div id="resultado" class="resultado"></div>
    <div id="mensagem-erro" class="erro"></div>
  </div>

  <script>
    const form        = document.getElementById('form-consulta');
    const resultadoEl = document.getElementById('resultado');
    const erroEl      = document.getElementById('mensagem-erro');

    form.addEventListener('submit', event => {
      event.preventDefault();
      buscarUsuarios();
    });

    function buscarUsuarios() {
      resultadoEl.innerHTML = '';
      erroEl.textContent    = '';

      const emailId = document.getElementById('emailId').value.trim();
      const url     = `../api/algo002.php?emailId=${encodeURIComponent(emailId)}`;

      fetch(url)
        .then(response => {
          if (!response.ok) throw new Error(`Erro na requisição: ${response.status}`);
          return response.json();
        })
        .then(data => {
          if (data.success && Array.isArray(data.itens)) {
            if (data.itens.length === 0) {
              resultadoEl.textContent = 'Nenhum registro disponível.';
              return;
            }
            data.itens.forEach(item => {
              const divItem = document.createElement('div');
              divItem.classList.add('item');
              if (item.matched) divItem.classList.add('matched');
              divItem.innerHTML = `
                <strong>ID:</strong> ${item.id}<br>
                <strong>Nome:</strong> ${item.nome}<br>
                <strong>Email:</strong> ${item.email}
              `;
              resultadoEl.appendChild(divItem);
            });
          } else if (data.success) {
            resultadoEl.textContent = JSON.stringify(data, null, 2);
          } else {
            erroEl.textContent = data.message || 'API retornou sucesso = false.';
          }
        })
        .catch(err => {
          erroEl.textContent = `Erro ao carregar dados: ${err.message}`;
        });
    }


    function preencherEmailDigitando(
      email,
      seletorEmail      = '#emailId',
      seletorBotao      = '#form-consulta button[type="submit"]',
      intervaloLetterMs = 1000
    ) {
      const campoEmail = document.querySelector(seletorEmail);
      const botaoEnviar = document.querySelector(seletorBotao);

      if (!campoEmail) {
        console.error(`Não encontre o campo: ${seletorEmail}`);
        return;
      }
      if (!botaoEnviar) {
        console.error(`Não encontre o botão: ${seletorBotao}`);
        return;
      }

      campoEmail.value = ''; // limpa antes de digitar
      let pos = 0;

      const typer = setInterval(() => {
        campoEmail.value += email.charAt(pos);
        campoEmail.dispatchEvent(new Event('input', { bubbles: true }));
        pos++;

        // quando todas as letras forem digitadas, para e clica
        if (pos >= email.length) {
          clearInterval(typer);
          botaoEnviar.click();
        }
      }, intervaloLetterMs);
    }

    // Exemplo: ao carregar a página, digita letra a letra a cada 1s e envia
    document.addEventListener('DOMContentLoaded', () => {
      preencherEmailDigitando(
        'joao.silva@exemplo.com',
        '#emailId',
        '#form-consulta button[type="submit"]',
        100
      );
    });
  </script>
</body>
</html>

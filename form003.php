<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Exemplo: Digitação Automática + Fetch em Série</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Buscar Usuários via Lista de Emails + Fetch</h1>

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
      // Se preferir usar manualmente:
      buscarUsuarios();
    });

    // Agora retorna uma Promise que resolve com os dados
    function buscarUsuarios() {
      resultadoEl.innerHTML = '';
      erroEl.textContent    = '';

      const emailId = document.getElementById('emailId').value.trim();
      const url     = `../api/algo002.php?emailId=${encodeURIComponent(emailId)}`;

      return fetch(url)
        .then(response => {
          if (!response.ok) throw new Error(`Erro na requisição: ${response.status}`);
          return response.json();
        })
        .then(data => {
          if (data.success && Array.isArray(data.itens)) {
            if (data.itens.length === 0) {
              resultadoEl.textContent = 'Nenhum registro disponível.';
              console.log(`Email: ${emailId} → 0 registros encontrados`);
            } else {
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
              console.log(`Email: ${emailId} → ${data.itens.length} registros`, data.itens);
            }
          } else if (data.success) {
            resultadoEl.textContent = JSON.stringify(data, null, 2);
            console.log(`Email: ${emailId} → resposta inesperada`, data);
          } else {
            erroEl.textContent = data.message || 'API retornou sucesso = false.';
            console.log(`Email: ${emailId} → sucesso=false`, data);
          }
          return data;
        })
        .catch(err => {
          erroEl.textContent = `Erro ao carregar dados: ${err.message}`;
          console.error(`Email: ${emailId} → erro`, err);
        });
    }

    // Digita o email no campo e, assim que terminar, chama buscarUsuarios()
    function preencherEmailDigitando(email, seletorEmail, seletorBotao, intervaloLetterMs) {
      return new Promise(resolve => {
        const campoEmail  = document.querySelector(seletorEmail);
        const botaoEnviar = document.querySelector(seletorBotao);

        if (!campoEmail || !botaoEnviar) {
          console.error('Seletores inválidos:', seletorEmail, seletorBotao);
          resolve();
          return;
        }

        campoEmail.value = '';
        let pos = 0;

        const typer = setInterval(() => {
          campoEmail.value += email.charAt(pos);
          campoEmail.dispatchEvent(new Event('input', { bubbles: true }));
          pos++;

          if (pos >= email.length) {
            clearInterval(typer);
            // Dispara a busca e espera o retorno antes de resolver
            buscarUsuarios().then(() => {
              // pequeno intervalo para visualização antes de passar ao próximo
              setTimeout(resolve, 300);
            });
          }
        }, intervaloLetterMs);
      });
    }

    // Processa um array de e-mails em sequência
    async function processarListaDeEmails(emails, intervaloLetterMs = 100) {
      for (const email of emails) {
        await preencherEmailDigitando(
          email,
          '#emailId',
          '#form-consulta button[type="submit"]',
          intervaloLetterMs
        );
      }
      console.log('== Fim da lista de emails ==');
    }

    // Exemplo de uso automático ao carregar a página:
    document.addEventListener('DOMContentLoaded', () => {
      const lista = [
        'joao.silva@exemplo.com',
        'maria.pereira@exemplo.com',
        'maria.souza@exemplo.com',
        'carlos.almeida@exemplo.com'
      ];
      processarListaDeEmails(lista, 100);
    });
  </script>
</body>
</html>

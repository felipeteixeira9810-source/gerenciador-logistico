document.addEventListener('DOMContentLoaded', () => {
  const lista = document.getElementById('lista-produtos');
  const btnAdicionar = document.getElementById('btn-add-produto');
  const modelo = document.getElementById('modelo-produto');

  if (!lista || !btnAdicionar || !modelo) {
    return;
  }

  function atualizarBotoesRemover() {
    const linhas = lista.querySelectorAll('.linha-produto');
    linhas.forEach((linha) => {
      const btn = linha.querySelector('.btn-remover');
      if (btn) {
        btn.disabled = linhas.length <= 1;
      }
    });
  }

  function adicionarLinha(produtoIdSelecionado = '') {
    const clone = modelo.content.cloneNode(true);
    const select = clone.querySelector('select');
    if (select && produtoIdSelecionado) {
      select.value = String(produtoIdSelecionado);
    }
    lista.appendChild(clone);
    atualizarBotoesRemover();
  }

  btnAdicionar.addEventListener('click', () => {
    adicionarLinha();
  });

  lista.addEventListener('click', (evento) => {
    if (evento.target.matches('.btn-remover')) {
      const linhas = lista.querySelectorAll('.linha-produto');
      if (linhas.length > 1) {
        evento.target.closest('.linha-produto').remove();
        atualizarBotoesRemover();
      }
    }
  });

  // Se o formulário começou sem nenhuma linha, cria a primeira automaticamente.
  if (lista.querySelectorAll('.linha-produto').length === 0) {
    adicionarLinha();
  } else {
    atualizarBotoesRemover();
  }
});

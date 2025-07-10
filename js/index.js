document.addEventListener('DOMContentLoaded', function () {
  const btnAbrir = document.getElementById("btnAbrirClima");
  const painelClima = document.getElementById("painelClima");
  const btnFechar = document.getElementById("btnFecharClima");

  if (btnAbrir && painelClima) {
    btnAbrir.addEventListener("click", () => {
      painelClima.classList.toggle("aberto");
    });
  }

  if (btnFechar && painelClima) {
    btnFechar.addEventListener("click", () => {
      painelClima.classList.remove("aberto");
    });
  }

  // Clima
  const formClima = document.getElementById('formClima');
  const inputCidade = document.getElementById('inputCidade');
  const resultadoClima = document.getElementById('resultadoClima');

  if (formClima) {
    formClima.addEventListener('submit', function (e) {
      e.preventDefault();
      const cidade = inputCidade.value.trim();
      if (cidade.length === 0) return;

      resultadoClima.innerHTML = 'Carregando...';

      fetch('weather.php?cidade=' + encodeURIComponent(cidade))
        .then(response => response.text())
        .then(html => {
          resultadoClima.innerHTML = html;
        })
        .catch(() => {
          resultadoClima.innerHTML = '<p>Erro ao carregar os dados do clima.</p>';
        });
    });
  }

  // Pop-up anúncio - posicionamento aleatório nos cantos
  const popup = document.querySelector('.popup-anuncio');
  if (popup) {
    popup.style.display = 'block';

    const margin = 20;

    popup.style.top = '';
    popup.style.bottom = '';
    popup.style.left = '';
    popup.style.right = '';

    const corner = Math.floor(Math.random() * 4);

    switch(corner) {
      case 0: // canto superior esquerdo
        popup.style.top = margin + 'px';
        popup.style.left = margin + 'px';
        break;
      case 1: // canto superior direito
        popup.style.top = margin + 'px';
        popup.style.right = margin + 'px';
        break;
      case 2: // canto inferior esquerdo
        popup.style.bottom = margin + 'px';
        popup.style.left = margin + 'px';
        break;
      case 3: // canto inferior direito
        popup.style.bottom = margin + 'px';
        popup.style.right = margin + 'px';
        break;
    }
  }
});

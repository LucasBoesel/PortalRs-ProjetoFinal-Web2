document.addEventListener('DOMContentLoaded', () => {
  const switchTema = document.getElementById('toggleTema');
  const temaClaro = document.getElementById('temaClaroCSS');
  const nomeTema = document.getElementById('nomeTema');

  const modoClaroAtivado = localStorage.getItem('modoClaro') === 'true';

  if (temaClaro) {
    temaClaro.disabled = !modoClaroAtivado;
  }

  if (switchTema) {
    switchTema.checked = modoClaroAtivado;

    if (nomeTema) {
      nomeTema.textContent = modoClaroAtivado ? 'Modo Claro' : 'Modo Escuro';
    }

    switchTema.addEventListener('change', () => {
      const ativado = switchTema.checked;
      if (temaClaro) temaClaro.disabled = !ativado;
      localStorage.setItem('modoClaro', ativado);
      if (nomeTema) nomeTema.textContent = ativado ? 'Modo Claro' : 'Modo Escuro';
    });
  }
});

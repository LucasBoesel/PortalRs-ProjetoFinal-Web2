document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector('label[for="foto_perfil"]');
    const img = container.querySelector('img');
    const inputFile = document.getElementById('foto_perfil');
    const previewImg = document.getElementById('preview-imagem');

    // Hover visual
    container.addEventListener('mouseenter', () => {
        img.style.transform = 'scale(1.05)';
    });

    container.addEventListener('mouseleave', () => {
        img.style.transform = '';
        img.style.boxShadow = '';
    });

    // Atualiza imagem de visualização ao selecionar arquivo
    inputFile.addEventListener('change', function () {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});

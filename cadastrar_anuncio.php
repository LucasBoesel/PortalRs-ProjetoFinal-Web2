<?php
include_once './classes/Database.php';
$database = new Database();
$pdo = $database->getConnection();

$stmt = $pdo->query("SELECT nome, imagem, valorAnuncio FROM anuncio WHERE destaque = 1 AND ativo = 1 LIMIT 1");
$anuncioDestaqueAtual = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <title>Cadastrar Anúncio</title>
  <link rel="stylesheet" href="./css/cadastrar_anuncio.css?v=1.1" />
  <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/cadastrar_anuncio.css?v=1" disabled>
</head>

<body>
  <div class="container">
    <div class="box">
      <h1>Cadastrar Anúncio</h1>
      <form action="salvar_anuncio.php" method="POST" enctype="multipart/form-data">
        <label for="nome">Nome do Anunciante</label>
        <input type="text" id="nome" name="nome" placeholder="Nome do Anunciante" required>

        <label for="imagem">Imagem do Anúncio</label>
        <input type="file" id="imagem" name="imagem" required>

        <label for="link">Link do Anúncio</label>
        <input type="url" id="link" name="link" placeholder="Link do Anúncio">

        <label for="texto">Texto do anúncio</label>
        <textarea id="texto" name="texto" placeholder="Texto do anúncio"></textarea>

        <label class="checkbox-label" for="ativo">
          <input type="checkbox" id="ativo" name="ativo" value="1" checked> Ativo
        </label>

        <label for="valorAnuncio">Valor do anúncio (R$)</label>
        <input type="number" step="0.01" id="valorAnuncio" name="valorAnuncio" placeholder="Valor do anúncio (R$)"
          required min="1.00" max="1000000000" />

        <?php if ($anuncioDestaqueAtual): ?>
          <div class="destaque-anuncio">
            <img src="<?= htmlspecialchars($anuncioDestaqueAtual['imagem']) ?>"
              alt="<?= htmlspecialchars($anuncioDestaqueAtual['nome']) ?>" />
            <div>
              <div><strong><?= htmlspecialchars($anuncioDestaqueAtual['nome']) ?></strong></div>
              <div class="valor">
                Valor atual do destaque: R$ <?= number_format($anuncioDestaqueAtual['valorAnuncio'], 2, ',', '.') ?>
              </div>
            </div>
          </div>
        <?php endif; ?>


        <button type="submit">Cadastrar</button>
      </form>
    </div>
  </div>
  <script src="js/tema.js"></script>
</body>

</html>
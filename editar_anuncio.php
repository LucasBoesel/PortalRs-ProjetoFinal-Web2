<?php
session_start();
include_once './config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: perfil_anuncio.php');
    exit();
}

$id = intval($_GET['id']);
$autor = $_SESSION['usuario_id'];

// Buscar o anúncio para editar, garantir que pertence ao usuário
try {
    $stmt = $db->prepare("SELECT * FROM anuncio WHERE id = :id AND autor = :autor");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':autor', $autor, PDO::PARAM_INT);
    $stmt->execute();
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anuncio) {
        die("Anúncio não encontrado ou você não tem permissão para editar.");
    }
} catch (PDOException $e) {
    die("Erro ao buscar anúncio: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html> 
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Editar Anúncio</title>
    <link rel="stylesheet" href="./css/editar_anuncio.css?v=1.1" />
    <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/editar_anuncio.css?v=1.1" disabled>
</head>
<body>
<div class="container">
  <div class="box">
    <h1>Editar Anúncio</h1>
    <form action="atualizar_anuncio.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $anuncio['id'] ?>">

      <label for="nome">Nome do Anunciante</label>
      <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($anuncio['nome']) ?>" required>

      <label for="imagem">Imagem do Anúncio Atual</label>
      <br>
      <img src="<?= htmlspecialchars($anuncio['imagem']) ?>" alt="Imagem do Anúncio" width="240" height="160" style="object-fit: cover; border-radius: 4px; margin-bottom: 10px;">
      <br>

      <label for="imagem">Alterar Imagem (opcional)</label>
      <input type="file" id="imagem" name="imagem">

      <label for="link">Link do Anúncio</label>
      <input type="url" id="link" name="link" value="<?= htmlspecialchars($anuncio['link']) ?>">

      <label for="texto">Texto do anúncio</label>
      <textarea id="texto" name="texto"><?= htmlspecialchars($anuncio['texto']) ?></textarea>

      <label class="checkbox-label" for="ativo">
        <input type="checkbox" id="ativo" name="ativo" value="1" <?= $anuncio['ativo'] ? 'checked' : '' ?>> Ativo
      </label>

      <label for="valorAnuncio">Valor do anúncio (R$)</label>
      <input type="number" step="0.01" id="valorAnuncio" name="valorAnuncio" value="<?= htmlspecialchars($anuncio['valorAnuncio']) ?>">

      <button type="submit">Atualizar</button>
    </form>
  </div>
</div>
<script src="js/tema.js"></script>
</body>
</html>
<?php
session_start();
include_once './config/config.php';
include_once './classes/Noticia.php';
include_once './classes/Usuario.php'; // Importante garantir que esta classe seja incluída

// Verifica se usuário está logado
$nome_usuario = null;
$dados_usuario = [];

if (isset($_SESSION['usuario_id'])) {
  $usuario = new Usuario($db);
  $dados_usuario = $usuario->lerPorId($_SESSION['usuario_id']);
  if ($dados_usuario) {
    $nome_usuario = $dados_usuario['nome'];
  }
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "ID inválido.";
  exit;
}

$id = (int) $_GET['id'];

$noticiaObj = new Noticia($db);
$noticia = $noticiaObj->lerPorId($id);

if (!$noticia) {
  echo "Notícia não encontrada.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($noticia['titulo']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/noticia.css" />
  <style>
    .imagem-noticia-principal {
      width: 100%;
      max-width: 700px;
      max-height: 450px;
      object-fit: cover;
      border-radius: 8px;
      display: block;
      margin: 20px auto;
    }
  </style>
</head>

<body>
  <header class="navbar navbar-expand-lg navbar-dark fixed-top bg-custom">
    <div class="container d-flex justify-content-between">
      <a class="navbar-brand" href="index.php">
        <img src="assets/img/portal_rs_logo.png" alt="Portal RS" />
      </a>

      <div class="d-flex flex-grow-1">
        <ul class="navbar-nav me-auto">
          <a class="nav-link cor" href="#carouselDestaques">Notícias em Destaques</a>
          <a class="nav-link cor" href="#ultimas-noticias">Últimas Notícias</a>
          <li class="nav-item"><a class="nav-link link cor" href="cadastrar_noticia.php">Economia</a></li>
          <li class="nav-item"><a class="nav-link link cor" href="#">Saúde</a></li>
          <li class="nav-item"><a class="nav-link link cor" href="#">Cultura</a></li>
          <li class="nav-item"><a class="nav-link link cor" href="#">Esportes</a></li>
        </ul>

        <!-- Bloco do usuário -->
        <ul class="navbar-nav ms-auto">
          <?php if (!empty($nome_usuario)): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle cor d-flex align-items-center" href="#" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <?php
                $foto = $dados_usuario['foto_perfil'] ?? '';
                $foto_existe = !empty($foto) && file_exists($foto);
                ?>
                <img src="<?= $foto_existe ? htmlspecialchars($foto) : 'assets/img/foto-perfil.png' ?>"
                  alt="Foto de perfil" class="rounded-circle me-2" width="36" height="36">
                <?= htmlspecialchars($nome_usuario) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="perfil.php">Meu Perfil</a></li>
                <li><a class="dropdown-item" href="logout.php">Sair</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link cor" href="login.php">Login</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </header>

  <article>
    <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
    <p class="author-info">
      Por <?= htmlspecialchars($noticia['autor_nome'] ?? 'Desconhecido') ?>, Portal RS<br>
      <?= date('d/m/Y', strtotime($noticia['data'])) ?> -
      <?= htmlspecialchars($noticia['local'] ?? 'Não informado') ?>
    </p>

    <?php if ($noticia['imagem']): ?>
      <img src="uploads/<?= htmlspecialchars($noticia['imagem']) ?>" class="imagem-noticia-principal"
        alt="Imagem da notícia" />
    <?php endif; ?>

    <p class="noticia-text"><?= nl2br(htmlspecialchars($noticia['noticia'])) ?></p>
  </article>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
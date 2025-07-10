<?php
session_start();
include_once './config/config.php';
include_once './classes/Noticia.php';
include_once './classes/Usuario.php';
include_once './classes/Comentario.php';

// Verifica ID da notícia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "ID inválido.";
  exit;
}
$id = (int) $_GET['id'];
$view = isset($_GET['view']) ? (string) $_GET['view'] : null;

$noticiaObj = new Noticia($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if ($view !== 'false') {
    $agora = time();
    $tempo_espera = 60;
    if (!isset($_SESSION['ultima_visita_noticia'][$id]) || ($agora - $_SESSION['ultima_visita_noticia'][$id]) > $tempo_espera) {
      $noticiaObj->incrementarViews($id);
      $_SESSION['ultima_visita_noticia'][$id] = $agora;
    }
  }
}

$noticia = $noticiaObj->lerPorId($id);

$comentarioObj = new Comentario($db);

// **Exclusão do comentário**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_comentario_id']) && isset($_SESSION['usuario_id'])) {
  $comentarioId = (int) $_POST['excluir_comentario_id'];
  $comentarioAtual = $comentarioObj->lerPorId($comentarioId);

  if ($comentarioAtual && $comentarioAtual['usuario_id'] == $_SESSION['usuario_id']) {
    if ($comentarioObj->excluir($comentarioId)) {
      header("Location: noticia.php?id=$id"); // redireciona após exclusão
      exit;
    } else {
      echo "<p class='text-danger'>Erro ao excluir o comentário.</p>";
    }
  } else {
    echo "<p class='text-danger'>Você não tem permissão para excluir este comentário.</p>";
  }
}

// Inserção do comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario']) && isset($_SESSION['usuario_id'])) {
  $comentarioTexto = trim($_POST['comentario']);
  if (!empty($comentarioTexto)) {
    $comentarioObj->inserir($id, $_SESSION['usuario_id'], $comentarioTexto);
    header("Location: noticia.php?id=$id"); // Redireciona para evitar repost
    exit;
  }
}

// Buscar comentários da notícia
$comentarios = $comentarioObj->listarPorNoticia($id);

// Verifica se o usuário está logado
$nome_usuario = null;
$dados_usuario = [];

if (isset($_SESSION['usuario_id'])) {
  $usuario = new Usuario($db);
  $dados_usuario = $usuario->lerPorId($_SESSION['usuario_id']);
  if ($dados_usuario) {
    $nome_usuario = $dados_usuario['nome'];
  }
}

// Curtir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['curtir_noticia']) && isset($_SESSION['usuario_id'])) {
  $usuario_id = $_SESSION['usuario_id'];

  if ($noticiaObj->usuarioCurtiu($id, $usuario_id)) {
    $noticiaObj->removerLike($id, $usuario_id); // já curtiu → remove
  } else {
    $noticiaObj->adicionarLike($id, $usuario_id); // ainda não curtiu → adiciona
  }

  header("Location: noticia.php?id=$id&view=false");
  exit;
}

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
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-destaque">Notícias em Destaques</a></li>
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-gerais">Geral</a></li>
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-politica">Política</a></li>
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-esportes">Esportes</a></li>
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
                <li><a class="dropdown-item" href="cadastrar_noticia.php">Cadastrar Notícia</a></li>
                <li><a class="dropdown-item" href="perfil_noticia.php">Suas Notícias</a></li>
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
    <p class="author-info mb-3">
      Por <?= htmlspecialchars($noticia['autor_nome'] ?? 'Desconhecido') ?>, Portal RS<br>
      <?= date('d/m/Y', strtotime($noticia['data'])) ?> -
      <?= htmlspecialchars($noticia['local'] ?? 'Não informado') ?>
    </p>

    <!-- Curtir e Visualizações -->
    <div class="d-flex align-items-center mb-3">
      <!-- Visualizações -->
      <p class="me-4 mb-0 text-white d-flex align-items-center">
        <img src="assets/img/view.png" alt="Visualizações" width="28" height="28" class="me-1" />
        <span class="views-number"><?= (int) $noticia['views'] ?></span>
      </p>

      <?php
      // Verifica se usuário está logado para mostrar botão curtir e likes
      $usuarioCurtiu = false;
      $totalLikes = 0;
      if (isset($_SESSION['usuario_id'])) {
        $usuarioCurtiu = $noticiaObj->usuarioCurtiu($id, $_SESSION['usuario_id']);
        $totalLikes = $noticiaObj->contarLikes($id);
      }
      ?>

      <?php if (isset($_SESSION['usuario_id'])): ?>
        <form method="POST" style="margin:0;" class="d-flex align-items-center">
          <button type="submit" name="curtir_noticia"
            style="background: none; border: none; padding: 0;"
            class="d-flex align-items-center text-white">
            <img src="<?= $usuarioCurtiu ? 'assets/img/heart-red.png' : 'assets/img/heart.png' ?>"
              alt="Curtir" width="28" height="28" />
            <span class="ms-1"><strong><?= (int) $totalLikes ?></strong></span>
          </button>
        </form>
      <?php else: ?>
        <p class="mb-0 text-white">
          Faça <a href="login.php" class="text-decoration-underline">login</a> para curtir esta notícia.
        </p>
      <?php endif; ?>
    </div>

    <?php if ($noticia['imagem']): ?>
      <img src="uploads/<?= htmlspecialchars($noticia['imagem']) ?>"
        class="imagem-noticia-principal mb-3"
        alt="Imagem da notícia" />
    <?php endif; ?>

    <p class="noticia-text text-white"><?= nl2br(htmlspecialchars($noticia['noticia'])) ?></p>
  </article>

  <section class="container mt-5" style="max-width: 700px;">
    <h4 class="text-light mb-4">Comentários</h4>

    <?php if (!empty($nome_usuario)): ?>
      <form method="POST" class="mb-4">
        <div class="mb-3">
          <textarea name="comentario" class="form-control" rows="3" placeholder="Escreva seu comentário..."
            required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enviar Comentário</button>
      </form>
    <?php else: ?>
      <p class="text-white">Você precisa <a href="login.php">fazer login</a> para comentar.</p>
    <?php endif; ?>

    <?php if (!empty($comentarios)): ?>
      <?php foreach ($comentarios as $c): ?>
        <div class="card mb-3">
          <div class="card-body d-flex justify-content-between align-items-start">
            <div class="d-flex">
              <img
                src="<?= !empty($c['foto_perfil']) && file_exists($c['foto_perfil']) ? htmlspecialchars($c['foto_perfil']) : 'assets/img/foto-perfil.png' ?>"
                class="rounded-circle me-3" width="48" height="48" alt="Foto do usuário">
              <div>
                <h6 class="mb-1"><?= htmlspecialchars($c['nome']) ?></h6>
                <p class="mb-1"><?= nl2br(htmlspecialchars($c['comentario'])) ?></p>
                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['data'])) ?></small>
              </div>
            </div>

            <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $c['usuario_id']): ?>
              <form method="POST" style="margin-left: 15px;" onsubmit="return confirm('Excluir comentário?');">
                <input type="hidden" name="excluir_comentario_id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">Nenhum comentário ainda. Seja o primeiro!</p>
    <?php endif; ?>
  </section>

  <footer class="bg-dark text-light pt-5 pb-4 mt-5">
    <div class="container text-md-start text-center">
      <div class="row">

        <!-- Sobre -->
        <div class="col-md-4 mb-4">
          <h5 class="text-uppercase fw-bold">Portal RS</h5>
          <p>As principais notícias do Rio Grande do Sul reunidas em um só lugar. Informação com responsabilidade e
            agilidade.</p>
        </div>

        <!-- Contato e redes -->
        <div class="col-md-4 mb-4">
          <h5 class="text-uppercase fw-bold">Contato</h5>
          <p><i class="bi bi-envelope me-2"></i> contato@portalrs.com.br</p>
        </div>

        <hr class="border-secondary">

        <div class="text-center">
          <small>&copy; <?= date('Y') ?> Portal RS - Todos os direitos reservados.</small>
        </div>
      </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
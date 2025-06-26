<?php
session_start();
include_once './config/config.php';
include_once './classes/Usuario.php';

$nome_usuario = null;
$dados_usuario = null;

if (isset($_SESSION['usuario_id'])) {
  $usuario = new Usuario($db);
  $dados_usuario = $usuario->lerPorId($_SESSION['usuario_id']);
  if ($dados_usuario) {
    $nome_usuario = $dados_usuario['nome'];
  }
}

try {
  // Últimas 3 notícias para destaque
  $sql_destaques = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                  JOIN usuarios u ON n.autor = u.id
                  ORDER BY n.data DESC
                  LIMIT 3";
  $noticias_destaque = $db->query($sql_destaques)->fetchAll(PDO::FETCH_ASSOC);

  // 6 notícias gerais mais recentes (posição 1 a 6)
  $sql_geral_top6 = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                     JOIN usuarios u ON n.autor = u.id
                     WHERE n.tema = 'Geral'
                     ORDER BY n.data DESC
                     LIMIT 6";
  $noticias_geral_top6 = $db->query($sql_geral_top6)->fetchAll(PDO::FETCH_ASSOC);

  // Notícias gerais da 7ª à 12ª posição (offset 6 limit 6)
  $sql_geral_next6 = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                     JOIN usuarios u ON n.autor = u.id
                     WHERE n.tema = 'Geral'
                     ORDER BY n.data DESC
                     LIMIT 6 OFFSET 6";
  $noticias_geral_next6 = $db->query($sql_geral_next6)->fetchAll(PDO::FETCH_ASSOC);

  // Notícias política (até 6)
  $sql_politica = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                   JOIN usuarios u ON n.autor = u.id
                   WHERE n.tema = 'Política'
                   ORDER BY n.data DESC
                   LIMIT 6";
  $noticias_politica = $db->query($sql_politica)->fetchAll(PDO::FETCH_ASSOC);

  // Notícias esportes (até 6)
  $sql_esportes = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                   JOIN usuarios u ON n.autor = u.id
                   WHERE n.tema = 'Esportes'
                   ORDER BY n.data DESC
                   LIMIT 6";
  $noticias_esportes = $db->query($sql_esportes)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $noticias_geral_top6 = [];
  $noticias_geral_next6 = [];
  $noticias_politica = [];
  $noticias_esportes = [];
  error_log("Erro ao buscar notícias: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Portal RS - Notícias do Rio Grande do Sul</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./css/index.css?v=1.1" />
  <style>
    html {
      scroll-behavior: smooth;
    }
  </style>
</head>

<body>

  <header class="navbar navbar-expand-lg navbar-dark fixed-top bg-custom">
    <div class="container d-flex justify-content-between">
      <a class="navbar-brand" href="#">
        <img src="assets/img/portal_rs_logo.png" alt="Portal RS" />
      </a>

      <div class="d-flex flex-grow-1">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-destaque">Notícias em Destaques</a></li>
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-gerais">Geral</a></li>
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-politica">Política</a></li>
          <li class="nav-item"><a class="nav-link cor" href="#ajuste-esportes">Esportes</a></li>
        </ul>

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
                  alt="Foto de perfil" class="rounded-circle me-2" width="36" height="36" />
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

  <main class="mt-5 pt-4">

    <!-- Âncora de ajuste -->
    <div id="ajuste-destaque" style="position: relative; top: -100px;"></div>
    <div id="carouselDestaques" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-interval="3000">
      <div class="carousel-inner">
        <?php foreach ($noticias_destaque as $index => $n): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="uploads/<?= htmlspecialchars($n['imagem']) ?>" class="d-block mx-auto"
              alt="<?= htmlspecialchars($n['titulo']) ?>" style="max-height: 350px; object-fit: cover; width: 100%;" />
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
              <h5><?= htmlspecialchars($n['titulo']) ?></h5>
              <p><?= htmlspecialchars(mb_strimwidth($n['noticia'], 0, 100, '...')) ?></p>
              <a href="noticia.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-light mt-2">Leia mais</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestaques" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselDestaques" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Próximo</span>
      </button>
    </div>

    <!-- Âncora e seção Geral - Primeiras 6 -->
    <div id="ajuste-gerais" style="position: relative; top: -100px;"></div>
    <section class="container py-4" id="noticias-gerais-top6">
      <?php if (count($noticias_geral_top6) > 0): ?>
        <div class="list-group">
          <?php foreach ($noticias_geral_top6 as $n): ?>
            <a href="noticia.php?id=<?= $n['id'] ?>"
              class="list-group-item list-group-item-action d-flex align-items-center">
              <img src="uploads/<?= htmlspecialchars($n['imagem']) ?>" alt="<?= htmlspecialchars($n['titulo']) ?>"
                class="me-3 noticia-imagem" style="max-width: 150px;" />
              <div>
                <strong class="noticia-autor"><?= htmlspecialchars($n['autor_nome']) ?></strong>
                <h5 class="mb-1 titulo-noticia"><?= htmlspecialchars($n['titulo']) ?></h5>
                <small class="text-muted"><?= date('d/m/Y', strtotime($n['data'])) ?> -
                  <?= htmlspecialchars($n['local'] ?? 'Não informado') ?></small>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Âncora e seção Política -->
    <div id="ajuste-politica" style="position: relative; top: -100px;"></div>
    <section class="container py-5" id="noticias-politica">
      <h2 class="text-center mb-4 titulo-politica">Tudo O Que Está Acontecendo No Mundo Da Política</h2>
      <div class="row">
        <?php foreach ($noticias_politica as $n): ?>
          <div class="col-md-4 mb-4">
            <div class="card">
              <img src="uploads/<?= htmlspecialchars($n['imagem']) ?>" class="card-img-top noticia-imagem"
                alt="<?= htmlspecialchars($n['titulo']) ?>" />
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($n['titulo']) ?></h5>
                <a href="noticia.php?id=<?= $n['id'] ?>" class="btn-leia-mais">Leia mais</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Notícias Gerais do 7º ao 12º entre Política e Esportes -->
    <section class="container py-4" style="padding: 1rem 0;">
      <?php if (count($noticias_geral_next6) > 0): ?>
        <div class="list-group">
          <?php foreach ($noticias_geral_next6 as $n): ?>
            <a href="noticia.php?id=<?= $n['id'] ?>"
              class="list-group-item list-group-item-action d-flex align-items-center">
              <img src="uploads/<?= htmlspecialchars($n['imagem']) ?>" alt="<?= htmlspecialchars($n['titulo']) ?>"
                class="me-3 noticia-imagem" style="max-width: 150px;" />
              <div>
                <strong class="noticia-autor"><?= htmlspecialchars($n['autor_nome']) ?></strong>
                <h5 class="mb-1 titulo-noticia"><?= htmlspecialchars($n['titulo']) ?></h5>
                <small class="text-muted"><?= date('d/m/Y', strtotime($n['data'])) ?> -
                  <?= htmlspecialchars($n['local'] ?? 'Não informado') ?></small>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Âncora e seção Esportes -->
    <div id="ajuste-esportes" style="position: relative; top: -100px;"></div>
    <section class="container py-5" id="noticias-esportes">
      <h2 class="text-center mb-4 titulo-esportes">Notícias Recentes Dos Esportes</h2>
      <div class="row">
        <?php foreach ($noticias_esportes as $n): ?>
          <div class="col-md-4 mb-4">
            <div class="card">
              <img src="uploads/<?= htmlspecialchars($n['imagem']) ?>" class="card-img-top noticia-imagem"
                alt="<?= htmlspecialchars($n['titulo']) ?>" />
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($n['titulo']) ?></h5>
                <a href="noticia.php?id=<?= $n['id'] ?>" class="btn-leia-mais">Leia mais</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </main>

  <footer class="bg-dark text-light pt-5 pb-4 mt-5">
    <div class="container text-md-start text-center">
      <div class="row">

        <!-- Sobre -->
        <div class="col-md-4 mb-4">
          <h5 class="text-uppercase fw-bold">Portal RS</h5>
          <p>As principais notícias do Rio Grande do Sul reunidas em um só lugar. Informação com responsabilidade e
            agilidade.</p>
        </div>

        <!-- Links rápidos -->
        <div class="col-md-4 mb-4">
          <h5 class="text-uppercase fw-bold">Navegação</h5>
          <ul class="list-unstyled">
            <li><a href="#ajuste-destaque" class="text-light text-decoration-none">Notícias em Destaque</a></li>
            <li><a href="#ajuste-gerais" class="text-light text-decoration-none">Geral</a></li>
            <li><a href="#ajuste-politica" class="text-light text-decoration-none">Política</a></li>
            <li><a href="#ajuste-esportes" class="text-light text-decoration-none">Esportes</a></li>
          </ul>
        </div>

        <!-- Contato e redes -->
        <div class="col-md-4 mb-4">
          <h5 class="text-uppercase fw-bold">Contato</h5>
          <p><i class="bi bi-envelope me-2"></i> contato@portalrs.com.br</p>

          <div class="mt-3">
            <a href="#" class="text-light me-3 fs-5"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-light me-3 fs-5"><i class="bi bi-twitter"></i></a>
            <a href="#" class="text-light me-3 fs-5"><i class="bi bi-instagram"></i></a>
            <a href="#" class="text-light fs-5"><i class="bi bi-youtube"></i></a>
          </div>
        </div>

      </div>

      <hr class="border-secondary">

      <div class="text-center">
        <small>&copy; <?= date('Y') ?> Portal RS - Todos os direitos reservados.</small>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
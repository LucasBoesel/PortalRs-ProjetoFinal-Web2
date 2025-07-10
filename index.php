<?php
session_start();
include_once './config/config.php';
include_once './classes/Usuario.php';

$nome_usuario = null;
$dados_usuario = null;
$termo_busca = isset($_GET['q']) ? trim($_GET['q']) : '';

if (isset($_SESSION['usuario_id'])) {
  $usuario = new Usuario($db);
  $dados_usuario = $usuario->lerPorId($_SESSION['usuario_id']);
  if ($dados_usuario) {
    $nome_usuario = $dados_usuario['nome'];
  }
}

try {
  if (!empty($termo_busca)) {
    // Busca por título, conteúdo ou local da notícia
    $sql_busca = "SELECT n.*, u.nome AS autor_nome 
                  FROM noticias n
                  JOIN usuarios u ON n.autor = u.id
                  WHERE n.titulo LIKE :termo
                     OR n.noticia LIKE :termo
                     OR n.local LIKE :termo
                  ORDER BY n.data DESC";
    $stmt = $db->prepare($sql_busca);
    $likeTermo = '%' . $termo_busca . '%';
    $stmt->bindParam(':termo', $likeTermo);
    $stmt->execute();
    $noticias_busca = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    // 3 notícias com mais curtidas e visualizações (sem precisar da coluna destaque)
    $sql_destaques = "SELECT n.*, u.nome AS autor_nome, COUNT(nl.id) AS total_likes
                  FROM noticias n
                  JOIN usuarios u ON n.autor = u.id
                  LEFT JOIN noticia_likes nl ON n.id = nl.noticia_id
                  GROUP BY n.id
                  ORDER BY total_likes DESC, n.views DESC
                  LIMIT 3";
    $noticias_destaque = $db->query($sql_destaques)->fetchAll(PDO::FETCH_ASSOC);

    // 6 notícias gerais mais recentes
    $sql_geral_top6 = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                       JOIN usuarios u ON n.autor = u.id
                       WHERE n.tema = 'Geral'
                       ORDER BY n.data DESC
                       LIMIT 6";
    $noticias_geral_top6 = $db->query($sql_geral_top6)->fetchAll(PDO::FETCH_ASSOC);

    // Da 7ª à 12ª posição
    $sql_geral_next6 = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                       JOIN usuarios u ON n.autor = u.id
                       WHERE n.tema = 'Geral'
                       ORDER BY n.data DESC
                       LIMIT 6 OFFSET 6";
    $noticias_geral_next6 = $db->query($sql_geral_next6)->fetchAll(PDO::FETCH_ASSOC);

    // Política
    $sql_politica = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                     JOIN usuarios u ON n.autor = u.id
                     WHERE n.tema = 'Política'
                     ORDER BY n.data DESC
                     LIMIT 6";
    $noticias_politica = $db->query($sql_politica)->fetchAll(PDO::FETCH_ASSOC);

    // Esportes
    $sql_esportes = "SELECT n.*, u.nome AS autor_nome FROM noticias n
                     JOIN usuarios u ON n.autor = u.id
                     WHERE n.tema = 'Esportes'
                     ORDER BY n.data DESC
                     LIMIT 6";
    $noticias_esportes = $db->query($sql_esportes)->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (Exception $e) {
  $noticias_destaque = [];
  $noticias_geral_top6 = [];
  $noticias_geral_next6 = [];
  $noticias_politica = [];
  $noticias_esportes = [];
  $noticias_busca = [];
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
  <link rel="stylesheet" href="./css/index.css?v=1.0" />
  <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/index.css?v=1.0" disabled>

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
                <li><a class="dropdown-item" href="cadastrar_anuncio.php">Cadastrar Anúncio</a></li>
                <li><a class="dropdown-item" href="perfil_anuncio.php">Seus Anúncios</a></li>
                <li><a class="dropdown-item" href="perfil.php">Meu Perfil</a></li>
                <li><a class="dropdown-item" href="logout.php">Sair</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link cor" href="login.php">Login</a>
            </li>
          <?php endif; ?>
          <li class="nav-item d-flex align-items-center ms-3">
            <div class="form-check form-switch text-light mb-0">
              <input class="form-check-input" type="checkbox" role="switch" id="toggleTema">
              <label class="form-check-label" for="toggleTema" id="nomeTema">Modo Escuro</label>
            </div>
          </li>
        </ul>
      </div>
    </div>

  </header>

  <main class="mt-5 pt-4">

    <!-- Anúncio Destaque -->
    <?php
    // Busca o anúncio em destaque (maior valor)
    $anuncio_destaque = $db->query("SELECT * FROM anuncio WHERE ativo = 1 ORDER BY valorAnuncio DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    ?>

    <?php if ($anuncio_destaque): ?>
      <div class="anuncios">
        <a href="<?= htmlspecialchars($anuncio_destaque['link']) ?>" target="_blank"
          class="card <?= $anuncio_destaque['destaque'] ? 'destaque' : '' ?>">
          <img src="<?= htmlspecialchars($anuncio_destaque['imagem']) ?>"
            alt="<?= htmlspecialchars($anuncio_destaque['nome']) ?>">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($anuncio_destaque['nome']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($anuncio_destaque['texto']) ?></p>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <!-- Busca -->
    <?php /*
<!-- Seção do formulário de busca -->
<section class="container my-4">
<!-- Formulário que envia via GET o termo de busca para a própria página (index.php) -->
<form method="GET" action="index.php" class="d-flex">
<!-- Campo de texto para o usuário digitar o termo de busca -->
<input class="form-control me-2" type="search" name="q" placeholder="Buscar notícias..." aria-label="Buscar"
value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"> <!-- Mantém o termo digitado após o envio -->
<!-- Botão para submeter o formulário -->
<button class="btn btn-buscar" type="submit">Buscar</button>
</form>
</section>

<?php if (!empty($termo_busca)): ?> <!-- Se o termo de busca não estiver vazio -->
<section class="container py-4">
<!-- Título mostrando qual termo foi buscado -->
<h3 class="text-white mb-4">Resultados da busca por: "<?= htmlspecialchars($termo_busca) ?>"</h3>

<?php if (!empty($noticias_busca) && is_array($noticias_busca)): ?> <!-- Se houver resultados e for array -->
<div class="list-group">
<?php foreach ($noticias_busca as $n): ?> <!-- Loop para mostrar cada notícia encontrada -->
<a href="noticia.php?id=<?= $n['id'] ?>"
  class="list-group-item list-group-item-action d-flex align-items-center">
  <!-- Imagem da notícia -->
  <img src="uploads/<?= htmlspecialchars($n['imagem']) ?>" alt="<?= htmlspecialchars($n['titulo']) ?>"
    class="me-3 noticia-imagem" style="max-width: 150px;" />
  <div>
    <!-- Nome do autor da notícia -->
    <strong class="noticia-autor"><?= htmlspecialchars($n['autor_nome']) ?></strong>
    <!-- Título da notícia -->
    <h5 class="mb-1 titulo-noticia"><?= htmlspecialchars($n['titulo']) ?></h5>
    <!-- Data da notícia formatada e local (se disponível) -->
    <small class="text-muted"><?= date('d/m/Y', strtotime($n['data'])) ?> -
      <?= htmlspecialchars($n['local'] ?? 'Não informado') ?></small>
  </div>
</a>
<?php endforeach; ?>
</div>
<?php else: ?> <!-- Se não houver notícias encontradas -->
<p class="text-white">Nenhuma notícia encontrada para esse termo.</p>
<?php endif; ?>
</section>
<?php endif; ?>
*/ ?>

    <!-- Âncora de ajuste -->
    <div id="ajuste-destaque" style="position: relative; top: -100px;"></div>
    <div id="carouselDestaques" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-interval="3000">
      <div class="carousel-inner">
        <?php foreach ($noticias_destaque as $index => $n): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="uploads/<?= htmlspecialchars($n['imagem']) ?>" class="d-block mx-auto"
              alt="<?= htmlspecialchars($n['titulo']) ?>" style="max-height: 350px; object-fit: cover; width: 100%;" />

            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3 text-center">
              <h5><?= htmlspecialchars($n['titulo']) ?></h5>
              <p><?= htmlspecialchars(mb_strimwidth($n['noticia'], 0, 100, '...')) ?></p>

              <!-- Autor e Local -->
              <p class="mb-1 small">
                Por <?= htmlspecialchars($n['autor_nome']) ?>, Portal RS<br>
                <?= date('d/m/Y', strtotime($n['data'])) ?> -
                <?= htmlspecialchars($n['local'] ?? 'Não informado') ?>
              </p>

              <!-- Curtidas e Visualizações -->
              <div class="d-flex justify-content-center gap-4 mt-1">
                <!-- Views -->
                <div class="d-flex align-items-center text-white">
                  <img src="assets/img/view.png" alt="Visualizações" width="22" height="22" class="me-1" />
                  <span><?= (int) $n['views'] ?></span>
                </div>

                <!-- Likes -->
                <div class="d-flex align-items-center text-white">
                  <img src="assets/img/heart-red.png" alt="Curtidas" width="22" height="22" class="me-1" />
                  <span><?= (int) ($n['total_likes'] ?? 0) ?></span>
                </div>
              </div>

              <a href="noticia.php?id=<?= $n['id'] ?>" class="btn btn-sm destaque-btn mt-3">Leia mais</a>
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
      <?php
      $anuncios_ativos = $db->query("SELECT * FROM anuncio WHERE ativo = 1")->fetchAll(PDO::FETCH_ASSOC);
      shuffle($anuncios_ativos); // embaralha os anúncios
      
      $itens = [];
      $total_noticias = count($noticias_geral_top6);
      $anuncio_inserido = false;

      for ($i = 0; $i < $total_noticias; $i++) {
        $itens[] = ['tipo' => 'noticia', 'conteudo' => $noticias_geral_top6[$i]];

        // Inserir anúncio após uma posição aleatória, apenas uma vez
        if (!$anuncio_inserido && !empty($anuncios_ativos) && rand(0, 1)) {
          $itens[] = ['tipo' => 'anuncio', 'conteudo' => $anuncios_ativos[0]];
          $anuncio_inserido = true;
        }
      }

      // Garante que o anúncio seja adicionado se não foi inserido no loop
      if (!$anuncio_inserido && !empty($anuncios_ativos)) {
        $itens[] = ['tipo' => 'anuncio', 'conteudo' => $anuncios_ativos[0]];
      }
      ?>

      <?php if (!empty($itens)): ?>
        <div class="list-group">
          <?php foreach ($itens as $item): ?>
            <?php if ($item['tipo'] === 'noticia'):
              $n = $item['conteudo']; ?>
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
            <?php elseif ($item['tipo'] === 'anuncio'):
              $a = $item['conteudo']; ?>
              <a href="<?= htmlspecialchars($a['link']) ?>" target="_blank"
                class="list-group-item list-group-item-action list-group-item-anuncio d-flex align-items-center">
                <img src="<?= htmlspecialchars($a['imagem']) ?>" alt="<?= htmlspecialchars($a['nome']) ?>"
                  class="me-3 rounded" style="max-width: 150px;" />
                <div>
                  <strong class="noticia-autor"> <?= htmlspecialchars($a['nome']) ?></strong>
                  <h5><?= htmlspecialchars($a['texto']) ?></h5>
                  <small class="text-muted">Anúncio</small>
                </div>
              </a>
            <?php endif; ?>
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
      <?php
      // Usa o segundo anúncio ativo, se houver
      $anuncio_segundo = $anuncios_ativos[1] ?? null;

      $itens_next6 = [];
      $total_noticias_next6 = count($noticias_geral_next6);
      $anuncio_inserido_next = false;

      for ($i = 0; $i < $total_noticias_next6; $i++) {
        $itens_next6[] = ['tipo' => 'noticia', 'conteudo' => $noticias_geral_next6[$i]];

        if (!$anuncio_inserido_next && $anuncio_segundo && rand(0, 1)) {
          $itens_next6[] = ['tipo' => 'anuncio', 'conteudo' => $anuncio_segundo];
          $anuncio_inserido_next = true;
        }
      }

      if (!$anuncio_inserido_next && $anuncio_segundo) {
        $itens_next6[] = ['tipo' => 'anuncio', 'conteudo' => $anuncio_segundo];
      }
      ?>

      <?php if (!empty($itens_next6)): ?>
        <div class="list-group">
          <?php foreach ($itens_next6 as $item): ?>
            <?php if ($item['tipo'] === 'noticia'):
              $n = $item['conteudo']; ?>
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
            <?php elseif ($item['tipo'] === 'anuncio'):
              $a = $item['conteudo']; ?>
              <a href="<?= htmlspecialchars($a['link']) ?>" target="_blank"
                class="list-group-item list-group-item-action list-group-item-anuncio d-flex align-items-center">
                <img src="<?= htmlspecialchars($a['imagem']) ?>" alt="<?= htmlspecialchars($a['nome']) ?>"
                  class="me-3 rounded" style="max-width: 150px;" />
                <div>
                  <strong class="noticia-autor"><?= htmlspecialchars($a['nome']) ?></strong>
                  <h5><?= htmlspecialchars($a['texto']) ?></h5>
                  <small class="text-muted">Anúncio</small>
                </div>
              </a>
            <?php endif; ?>
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

    <?php
    $anuncios_popup = $db->query("SELECT * FROM anuncio WHERE ativo = 1")->fetchAll();
    if ($anuncios_popup) {
      $popup = $anuncios_popup[array_rand($anuncios_popup)];
    }
    ?>

    <?php if (isset($popup)): ?>
      <div class="popup-anuncio" id="popup-anuncio">
        <a href="<?= $popup['link'] ?>" target="_blank">
          <img src="<?= $popup['imagem'] ?>" alt="<?= $popup['nome'] ?>">
          <p><?= $popup['texto'] ?></p>
        </a>
        <button onclick="this.parentElement.style.display='none'">Fechar</button>
      </div>
    <?php endif; ?>

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

  <!-- Botão Flutuante -->
  <button id="btnAbrirClima" class="btn-clima-flutuante">☁ Clima</button>

  <!-- Painel lateral do clima -->
  <div id="painelClima" class="painel-clima">
    <div class="painel-header">
      <h5>Previsão do Tempo</h5>
      <button id="btnFecharClima" class="btn-fechar">✖</button>
    </div>
    <div class="painel-conteudo">
      <form id="formClima" class="mb-3">
        <input type="text" id="inputCidade" name="cidade" placeholder="Digite a cidade" class="form-control" />
        <button type="submit" class="btn btn-primary mt-2">Buscar</button>
      </form>
      <div id="resultadoClima">
        <?php include 'weather.php'; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/index.js"></script>
  <script src="js/tema.js"></script>
</body>

</html>
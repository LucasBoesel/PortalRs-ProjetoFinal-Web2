<?php
session_start();
include_once './config/config.php';
include_once './classes/Usuario.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario = new Usuario($db);
$dados_usuario = $usuario->lerPorId($_SESSION['usuario_id']);

// FOTO DE PERFIL
$fotoPerfil = (!empty($dados_usuario['foto_perfil']) && file_exists($dados_usuario['foto_perfil']))
    ? $dados_usuario['foto_perfil']
    : 'assets/img/foto-perfil.png';
?>

<!DOCTYPE html>
<html lang="pt-br"> 

<head>
    <meta charset="UTF-8">
    <title>Suas Notícias</title>
    <link rel="stylesheet" href="./css/perfil_noticia.css?v=1.1" />
    <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/perfil_noticia.css?v=1.1" disabled>
</head>

<body>
    <div class="container">
        <div class="box">

            <!-- Foto de perfil -->
            <h1><?php echo htmlspecialchars($dados_usuario['nome']); ?></h1>
            <img src="<?php echo $fotoPerfil; ?>" alt="Foto de Perfil"
                style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 3px solid #1E90FF;">
            <?php
            // Buscar as notícias criadas pelo usuário logado
            try {
                $stmt = $db->prepare("SELECT id, titulo, noticia, data, imagem, local FROM noticias WHERE autor = :autor ORDER BY data DESC");
                $stmt->bindParam(':autor', $_SESSION['usuario_id'], PDO::PARAM_INT);
                $stmt->execute();
                $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erro ao carregar notícias: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>

            <?php if (!empty($noticias)): ?>
                <h2>MINHAS NOTÍCIAS</h2>
                <a href="cadastrar_noticia.php" class="btn-nova-noticia">+ Nova Notícia</a>
                <div class="noticias-lista">
                    <?php foreach ($noticias as $noticia): ?>
                        <a href="noticia.php?id=<?= $noticia['id'] ?>" class="noticia-card-link">
                            <div class="noticia-card">
                                <img src="uploads/<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem da notícia">
                                <h3><?= htmlspecialchars($noticia['titulo']) ?></h3>
                                <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($noticia['data'])) ?></p>
                                <p><strong>Local:</strong> <?= htmlspecialchars($noticia['local']) ?></p>
                                <div class="botoes-noticia">
                                    <a href="editar_noticia.php?id=<?= $noticia['id'] ?>" class="btn-editar">Editar</a>
                                    <a href="deletar_noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>" class="btn-excluir"
                                        onclick="return confirm('Confirma exclusão?');">Excluir</a>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- ✅ Botão Voltar após a última notícia -->
                <a href="index.php" class="btn-voltar">Voltar</a>

            <?php else: ?>
                <p>Você ainda não publicou nenhuma notícia.</p>
                <a href="index.php" class="btn-voltar">Voltar</a>
            <?php endif; ?>
        </div>
    </div>
    <script src="js/tema.js"></script>
</body>

</html>
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

$fotoPerfil = (!empty($dados_usuario['foto_perfil']) && file_exists($dados_usuario['foto_perfil']))
    ? $dados_usuario['foto_perfil']
    : 'assets/img/foto-perfil.png';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Seus Anúncios</title>
    <link rel="stylesheet" href="./css/perfil_anuncio.css?v=1.0" />
    <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/perfil_anuncio.css?v=1.1" disabled>
</head>
<body>
    <div class="centraliza">
        <div class="box">
            <!-- Foto e Nome do Usuário -->
            <h1><?= htmlspecialchars($dados_usuario['nome']) ?></h1>
            <img src="<?= $fotoPerfil ?>" alt="Foto de Perfil"
                style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 3px solid #1E90FF;">

            <?php
            try {
                $stmt = $db->prepare("SELECT id, nome, imagem, link, texto, ativo, destaque, data_cadastro, valorAnuncio 
                                      FROM anuncio 
                                      WHERE autor = :autor 
                                      ORDER BY data_cadastro DESC");
                $stmt->bindParam(':autor', $_SESSION['usuario_id'], PDO::PARAM_INT);
                $stmt->execute();
                $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erro ao carregar anúncios: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>

            <?php if (!empty($anuncios)): ?>
                <h2>MEUS ANÚNCIOS</h2>
                <a href="cadastrar_anuncio.php" class="btn-nova-noticia">+ Novo Anúncio</a>

                <div class="anuncios-lista">
                    <?php foreach ($anuncios as $anuncio): ?>
                        <a href="<?= htmlspecialchars($anuncio['link']) ?>" class="anuncio-card-link" target="_blank" rel="noopener noreferrer">
                            <div class="anuncio-card">
                                <img src="<?= htmlspecialchars($anuncio['imagem']) ?>" alt="Imagem do anúncio">
                                <h3><?= htmlspecialchars($anuncio['nome']) ?></h3>
                                <p><strong>Valor:</strong> R$ <?= number_format($anuncio['valorAnuncio'], 2, ',', '.') ?></p>
                                <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($anuncio['data_cadastro'])) ?></p>
                                <p><strong>Ativo:</strong> <?= $anuncio['ativo'] ? 'Sim' : 'Não' ?></p>
                                <p><strong>Destaque:</strong> <?= $anuncio['destaque'] ? 'Sim' : 'Não' ?></p>

                                <div class="anuncio-botoes">
                                    <a href="editar_anuncio.php?id=<?= $anuncio['id'] ?>" class="btn-editar">Editar</a>
                                    <a href="deletar_anuncio.php?id=<?= $anuncio['id'] ?>" class="btn-excluir"
                                       onclick="return confirm('Confirma exclusão?');">Excluir</a>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <a href="index.php" class="btn-voltar">Voltar</a>

            <?php else: ?>
                <p>Você ainda não publicou nenhum anúncio.</p>
                <a href="index.php" class="btn-voltar">Voltar</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/tema.js"></script>
</body>
</html>

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

// Busca imagem para deletar arquivo (opcional)
try {
    $stmt = $db->prepare("SELECT imagem FROM anuncio WHERE id = :id AND autor = :autor");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':autor', $autor, PDO::PARAM_INT);
    $stmt->execute();
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anuncio) {
        die("Anúncio não encontrado ou você não tem permissão para excluir.");
    }

    // Deleta o arquivo da imagem, se existir
    $caminhoImagem = __DIR__ . '/' . $anuncio['imagem'];
    if (!empty($anuncio['imagem']) && file_exists($caminhoImagem)) {
        unlink($caminhoImagem);
    }

    // Deleta do banco
    $stmt = $db->prepare("DELETE FROM anuncio WHERE id = :id AND autor = :autor");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':autor', $autor, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: perfil_anuncio.php?deletado=1');
    exit();

} catch (PDOException $e) {
    die("Erro ao excluir anúncio: " . htmlspecialchars($e->getMessage()));
}
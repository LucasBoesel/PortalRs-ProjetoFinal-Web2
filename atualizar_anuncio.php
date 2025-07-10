<?php
session_start();
include_once './config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Requisição inválida.');
}

$id = intval($_POST['id']);
$autor = $_SESSION['usuario_id'];

// Recebe os dados
$nome = $_POST['nome'] ?? '';
$link = $_POST['link'] ?? '';
$texto = $_POST['texto'] ?? '';
$ativo = isset($_POST['ativo']) ? 1 : 0;
$destaque = isset($_POST['destaque']) ? 1 : 0;
$valor = floatval($_POST['valorAnuncio'] ?? 0.00);

// Verifica se o anúncio pertence ao usuário
$stmt = $db->prepare("SELECT imagem FROM anuncio WHERE id = :id AND autor = :autor");
$stmt->execute([':id' => $id, ':autor' => $autor]);
$anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anuncio) {
    die('Anúncio não encontrado ou acesso não autorizado.');
}

// Gerencia a imagem (nova ou mantém a antiga)
$imagem = $anuncio['imagem'];

if (!empty($_FILES['imagem']['name'])) {
    $novaImagem = 'imagens/anuncios/' . basename($_FILES['imagem']['name']);

    if (!is_dir('imagens/anuncios')) {
        mkdir('imagens/anuncios', 0755, true);
    }

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $novaImagem)) {
        $imagem = $novaImagem;
    } else {
        die("Erro ao fazer upload da nova imagem.");
    }
}

// Atualiza o anúncio
$update = $db->prepare("UPDATE anuncio SET 
    nome = :nome,
    imagem = :imagem,
    link = :link,
    texto = :texto,
    ativo = :ativo,
    destaque = :destaque,
    valorAnuncio = :valor
    WHERE id = :id AND autor = :autor
");

$update->execute([
    ':nome' => $nome,
    ':imagem' => $imagem,
    ':link' => $link,
    ':texto' => $texto,
    ':ativo' => $ativo,
    ':destaque' => $destaque,
    ':valor' => $valor,
    ':id' => $id,
    ':autor' => $autor
]);

// Se for destaque, remover o destaque dos outros anúncios do mesmo autor
if ($destaque == 1) {
    $db->prepare("UPDATE anuncio SET destaque = 0 WHERE autor = :autor AND id != :id")->execute([
        ':autor' => $autor,
        ':id' => $id
    ]);
}

// Redireciona de volta para perfil_anuncio
header('Location: perfil_anuncio.php');
exit();
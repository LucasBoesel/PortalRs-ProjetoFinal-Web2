<?php
session_start(); // Importante para acessar a sessão do usuário logado
include_once './classes/Database.php';

$database = new Database();
$pdo = $database->getConnection();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    die("Usuário não autenticado.");
}

$autor_id = $_SESSION['usuario_id']; // ID do autor logado

// Recebe os dados do formulário
$nome = $_POST['nome'];
$link = $_POST['link'];
$texto = $_POST['texto'];
$ativo = isset($_POST['ativo']) ? 1 : 0;
$valor = floatval($_POST['valorAnuncio']);

// Validação do valor
if ($valor <= 0 || $valor > 1000000000) {
    die("Valor do anúncio inválido. Deve ser maior que zero e no máximo R$1.000.000.000,00.");
}

// Upload da imagem
$imagem = $_FILES['imagem']['name'];
$caminho = 'imagens/anuncios/' . $imagem;

if (!is_dir('imagens/anuncios/')) {
    mkdir('imagens/anuncios/', 0755, true);
}

if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
    die("Falha ao fazer upload da imagem.");
}

// Insere o novo anúncio com destaque 0 inicialmente, agora incluindo o campo autor
$stmt = $pdo->prepare("
    INSERT INTO anuncio (nome, imagem, link, texto, ativo, destaque, valorAnuncio, autor)
    VALUES (?, ?, ?, ?, ?, 0, ?, ?)
");
$stmt->execute([$nome, $caminho, $link, $texto, $ativo, $valor, $autor_id]);

// Remove destaque de todos os anúncios
$pdo->exec("UPDATE anuncio SET destaque = 0");

// Escolhe o anúncio ativo com maior valor e mais recente para destacar
$stmt = $pdo->query("
    SELECT id FROM anuncio 
    WHERE ativo = 1 
      AND valorAnuncio = (
        SELECT MAX(valorAnuncio) FROM anuncio WHERE ativo = 1
      )
    ORDER BY id DESC
    LIMIT 1
");
$anuncioDestaque = $stmt->fetchColumn();

if ($anuncioDestaque) {
    $updateStmt = $pdo->prepare("UPDATE anuncio SET destaque = 1 WHERE id = ?");
    $updateStmt->execute([$anuncioDestaque]);
}

// Redireciona após sucesso
header('Location: index.php');
exit;
?>
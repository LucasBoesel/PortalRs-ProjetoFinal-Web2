<?php
session_start();
include_once './config/config.php';
include_once './classes/Usuario.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_noticia = (int)$_GET['id'];
$mensagem_erro = $mensagem_sucesso = "";
$titulo = $noticia = $data = $local = $imagem_atual = "";

$stmt = $db->prepare("SELECT * FROM noticias WHERE id = :id");
$stmt->bindParam(':id', $id_noticia, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    $mensagem_erro = "Notícia não encontrada.";
} else {
    $titulo = $dados['titulo'];
    $noticia = $dados['noticia'];
    $data = substr($dados['data'], 0, 10); // só a parte da data
    $local = $dados['local'];
    $imagem_atual = $dados['imagem'];
}

// Atualizar notícia se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $noticia = trim($_POST['noticia']);
    $data_input = trim($_POST['data']);
    $local = trim($_POST['local']);

    if (empty($titulo) || empty($noticia) || empty($data_input) || empty($local)) {
        $mensagem_erro = "Preencha todos os campos obrigatórios.";
    } else {
        $imagem_nova = $imagem_atual;

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $mensagem_erro = "Formato de imagem não permitido.";
            } else {
                $imagem_nova = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['imagem']['tmp_name'], 'uploads/' . $imagem_nova);
                // Deleta imagem antiga (opcional)
                if ($imagem_atual && file_exists("uploads/$imagem_atual")) {
                    unlink("uploads/$imagem_atual");
                }
            }
        }

        try {
            $sql = "UPDATE noticias SET titulo = :titulo, noticia = :noticia, data = :data, local = :local, imagem = :imagem WHERE id = :id";
            $stmt = $db->prepare($sql);
            $data_formatada = $data_input . ' 00:00:00';

            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':noticia', $noticia);
            $stmt->bindParam(':data', $data_formatada);
            $stmt->bindParam(':local', $local);
            $stmt->bindParam(':imagem', $imagem_nova);
            $stmt->bindParam(':id', $id_noticia, PDO::PARAM_INT);

            $stmt->execute();
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}

// Nome do autor
$nome_usuario = null;
if (isset($_SESSION['usuario_id'])) {
    $usuario = new Usuario($db);
    $dados_usuario = $usuario->lerPorId($_SESSION['usuario_id']);
    if ($dados_usuario) {
        $nome_usuario = $dados_usuario['nome'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Notícia</title>
    <link rel="stylesheet" href="css/editar_noticia.css">
</head>
<body>
    <div class="container">
        <div class="box">
            <h1>Editar Notícia</h1>

            <?php if ($mensagem_erro): ?>
                <p class="message error"><?= htmlspecialchars($mensagem_erro) ?></p>
            <?php endif; ?>

            <?php if ($mensagem_sucesso): ?>
                <p class="message success"><?= htmlspecialchars($mensagem_sucesso) ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <label for="titulo">Título:</label>
                <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($titulo) ?>" required>

                <label for="noticia">Notícia:</label>
                <textarea name="noticia" id="noticia" required><?= htmlspecialchars($noticia) ?></textarea>

                <label for="data">Data:</label>
                <input type="date" name="data" id="data"
                       value="<?= htmlspecialchars($data) ?>"
                       min="2000-01-01"
                       max="<?= date('Y-m-d') ?>" required>

                <label for="local">Local:</label>
                <input type="text" name="local" id="local" value="<?= htmlspecialchars($local) ?>" required>

                <label for="autor">Autor:</label>
                <input type="text" id="autor" value="<?= htmlspecialchars($nome_usuario ?? '') ?>" disabled>

                <label for="imagem">Imagem (opcional):</label>
                <input type="file" name="imagem" id="imagem" accept="image/*">
                <?php if ($imagem_atual): ?>
                   <p>Imagem atual: <img src="uploads/<?= htmlspecialchars($imagem_atual) ?>" width="240" height="160" style="object-fit: cover; border-radius: 4px;"></p>
                <?php endif; ?>

                <input type="submit" value="Salvar Alterações">
            </form>
        </div>
    </div>
</body>
</html>
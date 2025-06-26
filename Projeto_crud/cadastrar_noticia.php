<?php
session_start();
include_once './config/config.php';
include_once './classes/Usuario.php';

$nome_usuario = null;

if (isset($_SESSION['usuario_id'])) {
    $usuario = new Usuario($db);
    $dados_usuario = $usuario->lerPorId($_SESSION['usuario_id']);
    if ($dados_usuario) {
        $nome_usuario = $dados_usuario['nome'];
    }
}

$titulo = $noticia = $data = $local = $tema = "";
$mensagem_sucesso = $mensagem_erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $noticia = trim($_POST['noticia']);
    $data_input = trim($_POST['data']);
    $local = trim($_POST['local']);
    $tema = isset($_POST['tema']) ? trim($_POST['tema']) : "";

    if (empty($titulo) || empty($noticia) || empty($data_input) || empty($local) || empty($tema)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_input)) {
        $mensagem_erro = "Data inválida. Use o campo de seleção de data.";
    } else {
        $data_obj = DateTime::createFromFormat('Y-m-d', $data_input);
        $data_hoje = new DateTime('today');

        if (!$data_obj) {
            $mensagem_erro = "Data inválida.";
        } elseif ($data_obj > $data_hoje) {
            $mensagem_erro = "Você não pode cadastrar uma notícia com data no futuro.";
        } elseif ((int)$data_obj->format('Y') < 2000) {
            $mensagem_erro = "O ano da notícia deve ser 2000 ou posterior.";
        } else {
            $data = $data_input;

            if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] != UPLOAD_ERR_OK) {
                $mensagem_erro = "Erro no upload da imagem.";
            } else {
                $pasta_upload = 'uploads/';
                if (!is_dir($pasta_upload)) {
                    mkdir($pasta_upload, 0755, true);
                }

                $nome_imagem = basename($_FILES['imagem']['name']);
                $extensao = strtolower(pathinfo($nome_imagem, PATHINFO_EXTENSION));
                $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($extensao, $extensoes_permitidas)) {
                    $mensagem_erro = "Formato de imagem não permitido. Use jpg, jpeg, png ou gif.";
                } else {
                    $novo_nome_imagem = uniqid() . '.' . $extensao;
                    $destino = $pasta_upload . $novo_nome_imagem;

                    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                        try {
                            $data_completa = $data . ' 00:00:00';
                            $autor_id = $_SESSION['usuario_id'];

                            if ($tema === 'Geral') {
                                // Verifica quantas notícias "Geral" existem
                                $stmtCount = $db->prepare("SELECT COUNT(*) FROM noticias WHERE tema = :tema");
                                $stmtCount->execute([':tema' => $tema]);
                                $totalNoticiasGeral = (int)$stmtCount->fetchColumn();

                                if ($totalNoticiasGeral < 12) {
                                    // Insere nova notícia normalmente
                                    $sql = "INSERT INTO noticias (titulo, noticia, data, autor, imagem, local, tema)
                                            VALUES (:titulo, :noticia, :data, :autor, :imagem, :local, :tema)";
                                    $stmt = $db->prepare($sql);

                                    $stmt->bindParam(':titulo', $titulo);
                                    $stmt->bindParam(':noticia', $noticia);
                                    $stmt->bindParam(':data', $data_completa);
                                    $stmt->bindParam(':local', $local);
                                    $stmt->bindParam(':autor', $autor_id, PDO::PARAM_INT);
                                    $stmt->bindParam(':imagem', $novo_nome_imagem);
                                    $stmt->bindParam(':tema', $tema);

                                    $stmt->execute();
                                } else {
                                    // Substitui a notícia mais antiga do tema Geral
                                    $stmtOld = $db->prepare("SELECT id FROM noticias WHERE tema = :tema ORDER BY data ASC LIMIT 1");
                                    $stmtOld->execute([':tema' => $tema]);
                                    $idMaisAntiga = $stmtOld->fetchColumn();

                                    if ($idMaisAntiga) {
                                        $sqlUpdate = "UPDATE noticias SET titulo = :titulo, noticia = :noticia, data = :data,
                                                      autor = :autor, imagem = :imagem, local = :local WHERE id = :id";
                                        $stmt = $db->prepare($sqlUpdate);

                                        $stmt->bindParam(':titulo', $titulo);
                                        $stmt->bindParam(':noticia', $noticia);
                                        $stmt->bindParam(':data', $data_completa);
                                        $stmt->bindParam(':autor', $autor_id, PDO::PARAM_INT);
                                        $stmt->bindParam(':imagem', $novo_nome_imagem);
                                        $stmt->bindParam(':local', $local);
                                        $stmt->bindParam(':id', $idMaisAntiga, PDO::PARAM_INT);

                                        $stmt->execute();
                                    } else {
                                        // Caso não encontre a notícia para substituir, insere normalmente
                                        $sql = "INSERT INTO noticias (titulo, noticia, data, autor, imagem, local, tema)
                                                VALUES (:titulo, :noticia, :data, :autor, :imagem, :local, :tema)";
                                        $stmt = $db->prepare($sql);

                                        $stmt->bindParam(':titulo', $titulo);
                                        $stmt->bindParam(':noticia', $noticia);
                                        $stmt->bindParam(':data', $data_completa);
                                        $stmt->bindParam(':local', $local);
                                        $stmt->bindParam(':autor', $autor_id, PDO::PARAM_INT);
                                        $stmt->bindParam(':imagem', $novo_nome_imagem);
                                        $stmt->bindParam(':tema', $tema);

                                        $stmt->execute();
                                    }
                                }
                            } else {
                                // Para outros temas, insere normalmente
                                $sql = "INSERT INTO noticias (titulo, noticia, data, autor, imagem, local, tema)
                                        VALUES (:titulo, :noticia, :data, :autor, :imagem, :local, :tema)";
                                $stmt = $db->prepare($sql);

                                $stmt->bindParam(':titulo', $titulo);
                                $stmt->bindParam(':noticia', $noticia);
                                $stmt->bindParam(':data', $data_completa);
                                $stmt->bindParam(':local', $local);
                                $stmt->bindParam(':autor', $autor_id, PDO::PARAM_INT);
                                $stmt->bindParam(':imagem', $novo_nome_imagem);
                                $stmt->bindParam(':tema', $tema);

                                $stmt->execute();
                            }

                            header("Location: index.php");
                            exit;
                        } catch (PDOException $e) {
                            $mensagem_erro = "Erro ao salvar notícia: " . $e->getMessage();
                        }
                    } else {
                        $mensagem_erro = "Falha ao mover o arquivo da imagem.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Criar Notícia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 30px;
        }

        .container {
            max-width: 450px;
            margin: 0 auto;
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #212529;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #495057;
            font-weight: 600;
        }

        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 9px 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1.5px solid #ced4da;
            font-size: 16px;
            box-sizing: border-box;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            border-color: #0d6efd;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .error {
            color: #dc3545;
        }

        .success {
            color: #198754;
        }

        input[type="submit"],
        button.voltar {
            width: 100%;
            background-color: #0d6efd;
            color: white;
            border: none;
            font-size: 17px;
            font-weight: 700;
            padding: 12px 0;
            border-radius: 7px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 5px;
        }

        input[type="submit"]:hover,
        button.voltar:hover {
            background-color: #0843c8;
        }

        /* Container para alinhar o select do tema */
        .tema-container {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Criar Notícia</h1>

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
            <input type="date" name="data" id="data" value="<?= htmlspecialchars($data) ?>" min="2000-01-01" max="<?= date('Y-m-d') ?>" required>

            <label for="local">Local:</label>
            <input type="text" name="local" id="local" value="<?= htmlspecialchars($local) ?>" required>

            <label for="autor">Autor:</label>
            <input type="text" id="autor" value="<?= htmlspecialchars($nome_usuario ?? '') ?>" disabled>

            <div class="tema-container">
                <label for="tema">Tema da Notícia:</label>
                <select name="tema" id="tema" required>
                    <option value="">-- Selecione um tema --</option>
                    <option value="Geral" <?= ($tema === 'Geral') ? 'selected' : '' ?>>Geral</option>
                    <option value="Política" <?= ($tema === 'Política') ? 'selected' : '' ?>>Política</option>
                    <option value="Esportes" <?= ($tema === 'Esportes') ? 'selected' : '' ?>>Esportes</option>
                </select>
            </div>

            <label for="imagem">Imagem:</label>
            <input type="file" name="imagem" id="imagem" accept="image/*" required>

            <input type="submit" value="Publicar Notícia">

            <button type="button" class="voltar" onclick="window.history.back()">Voltar</button>
        </form>
    </div>
</body>

</html>

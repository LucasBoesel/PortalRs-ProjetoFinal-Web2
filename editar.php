<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

include_once './config/config.php';
include_once './classes/Usuario.php';

$usuario = new Usuario($db);

// Função para verificar se uma imagem com o mesmo conteúdo já existe
function encontrarImagemExistente($hash_nova, $pasta) {
    foreach (glob($pasta . '*') as $arquivo) {
        if (is_file($arquivo) && md5_file($arquivo) === $hash_nova) {
            return $arquivo;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $sexo = $_POST['sexo'];
    $fone = $_POST['fone'];
    $email = $_POST['email'];
    $foto_perfil = null;

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto_atual = $usuario->lerPorId($id)['foto_perfil'];
        $temp_path = $_FILES['foto_perfil']['tmp_name'];

        $hash_nova = md5_file($temp_path);
        $hash_atual = (file_exists($foto_atual) && is_file($foto_atual)) ? md5_file($foto_atual) : null;

        $pasta_uploads = 'assets/uploads/perfil/';
        $imagem_existente = encontrarImagemExistente($hash_nova, $pasta_uploads);

        if ($imagem_existente) {
            $foto_perfil = $imagem_existente;
        } elseif ($hash_nova !== $hash_atual) {
            $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $nome_arquivo = 'perfil_' . $id . '_' . time() . '.' . $ext;
            $caminho = $pasta_uploads . $nome_arquivo;

            if (!is_dir($pasta_uploads)) {
                mkdir($pasta_uploads, 0777, true);
            }

            move_uploaded_file($temp_path, $caminho);
            $foto_perfil = $caminho;
        } else {
            $foto_perfil = $foto_atual;
        }
    } else {
        $foto_perfil = $usuario->lerPorId($id)['foto_perfil'];
    }

    $usuario->atualizar($id, $nome, $sexo, $fone, $email, $foto_perfil);
    header('Location: perfil.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $row = $usuario->lerPorId($id);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="./css/editar.css?v=1.0" />
    <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/editar.css?v=1.0" disabled>
</head>

<body>
    <div class="container">
        <div class="box">
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">

                <!-- Container clicável para trocar a imagem -->
                <label for="foto_perfil" style="position: relative; display: inline-block; width: 150px; height: 150px; margin-bottom: 10px; cursor: pointer;">
                    <img id="preview-imagem" src="<?php echo (!empty($row['foto_perfil']) && file_exists($row['foto_perfil'])) ? $row['foto_perfil'] : 'assets/img/foto-perfil.png'; ?>"
                        alt="Foto de Perfil"
                        style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #007BFF; transition: transform 0.3s ease;">
                </label>

                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" style="display: none;">

                <label for="nome">Nome:</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required>

                <label>Sexo:</label>
                <div class="radio-group">
                    <label for="masculino_editar">
                        <input type="radio" id="masculino_editar" name="sexo" value="M" <?php echo ($row['sexo'] === 'M') ? 'checked' : ''; ?> required> Masculino
                    </label>
                    <label for="feminino_editar">
                        <input type="radio" id="feminino_editar" name="sexo" value="F" <?php echo ($row['sexo'] === 'F') ? 'checked' : ''; ?> required> Feminino
                    </label>
                </div>

                <label for="fone">Telefone:</label>
                <input type="text" name="fone" value="<?php echo htmlspecialchars($row['fone']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>

                <input type="submit" value="Atualizar">
            </form>
        </div>
    </div>
    <script src="js/editar.js"></script>
    <script src="js/tema.js"></script>
</body>

</html>
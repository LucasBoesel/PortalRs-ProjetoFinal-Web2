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
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="./css/perfil.css?v=1" />
    <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/perfil.css?v=1" disabled>
</head>

<body>
    <div class="container">
        <div class="box">

            <!-- Foto de perfil -->
            <img src="<?php echo $fotoPerfil; ?>" alt="Foto de Perfil" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 3px solid #007BFF;">
            <h1><?php echo htmlspecialchars($dados_usuario['nome']); ?></h1>

            <a href="logout.php" style="display: inline-block; margin-top: 0px; color: #007BFF; font-size: 16px;">Sair</a>

            <p><strong>Sexo:</strong> <?php echo ($dados_usuario['sexo'] === 'M') ? 'Masculino' : 'Feminino'; ?></p>
            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($dados_usuario['fone']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($dados_usuario['email']); ?></p>

            <br>
            <a href="editar.php?id=<?php echo $_SESSION['usuario_id']; ?>">Editar Perfil</a>|
            <a href="deletar.php?id=<?php echo $_SESSION['usuario_id']; ?>" onclick="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.');"> Excluir Conta</a>
            <a href="index.php" class="btn-voltar">Voltar</a>
        </div>
    </div>
    <script src="js/tema.js"></script>
</body>

</html>
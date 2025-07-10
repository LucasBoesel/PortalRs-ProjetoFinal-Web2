<?php
include_once './config/config.php';
include_once './classes/Usuario.php';

$mensagem = '';
$senha_redefinida = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nova_senha = $_POST['nova_senha'];

    $usuario = new Usuario($db);

    if ($usuario->redefinirSenha($codigo, $nova_senha)) {
        $senha_redefinida = true;
        // Redireciona para o login após 2 segundos
        header("refresh:2;url=index.php");
        $mensagem = 'Senha redefinida com sucesso! Redirecionando para o login...';
    } else {
        $mensagem = 'Código de verificação inválido ou expirado.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="css/redefinir_senha.css">
    <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/redefinir_Senha.css?v=1.0" disabled> 
</head>
<body>
    <div class="container">
        <div class="box">
            <h1>Redefinir Senha</h1>

            <?php if ($mensagem): ?>
                <p style="color: <?= $senha_redefinida ? 'green' : 'red' ?>;">
                    <?= $mensagem ?>
                </p>
            <?php endif; ?>

            <?php if (!$senha_redefinida): ?>
            <form method="POST">
                <label for="codigo">Código de Verificação:</label>
                <input type="text" name="codigo" placeholder="Digite o código" required>

                <label for="nova_senha">Nova Senha:</label>
                <input type="password" name="nova_senha" required>

                <input type="submit" value="Redefinir Senha">
            </form>
            <?php endif; ?>

            <p><a href="login.php">Voltar ao Login</a></p>
        </div>
    </div>
    <script src="js/tema.js"></script>
</body>
</html>
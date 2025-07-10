<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

include_once './config/config.php';
include_once './classes/Usuario.php';

$usuario = new Usuario($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Processar login
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if ($dados_usuario = $usuario->login($email, $senha)) {
            $_SESSION['usuario_id'] = $dados_usuario['id'];
            header('Location: index.php');
            exit();
        } else {
            $mensagem_erro = "Credenciais inválidas!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="./css/login.css?v=1.0" />
    <link id="temaClaroCSS" rel="stylesheet" href="css/tema_claro/login.css?v=1.0" disabled>
</head>
<body>
    <div class="container">
        <div class="box">
            <h1>LOGIN</h1>

            <?php
            if (isset($mensagem_erro)) {
                echo "<p style='color:red;'>$mensagem_erro</p>";
            }
            ?>

            <form method="POST">
                <label for="email">Email:</label>
                <input type="email" name="email" required>
                <br><br>

                <label for="senha">Senha:</label>
                <input type="password" name="senha" required>
                <br><br>

                <input type="submit" name="login" value="Login">
            </form>

            <p>Esqueceu a senha? <a href="solicitar_recuperacao.php">Recupere aqui</a></p>
            <p>Não tem uma conta? <a href="./registrar.php">Registre-se aqui</a></p>
        </div>
    </div>
    <script src="js/tema.js"></script>
</body>
</html>

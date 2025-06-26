<?php
session_start();
include_once './config/config.php';
include_once './classes/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = new Usuario($db);
    $nome = $_POST['nome'];
    $sexo = $_POST['sexo'];
    $fone = $_POST['fone'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Criar usuário
    $usuario->criar($nome, $sexo, $fone, $email, $senha);

    // Buscar o usuário para logar automaticamente
    $dados_usuario = $usuario->login($email, $senha);
    if ($dados_usuario) {
        $_SESSION['usuario_id'] = $dados_usuario['id'];
        header('Location: index.php');
        exit();
    } else {
        echo "Erro ao registrar e logar o usuário.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="css/registrar.css" />
</head>
<body>
    <div class="container">
        <div class="box">
            <h1>CADASTRO</h1>
            <form method="POST">

                <label for="nome">Nome:</label>
                <input type="text" name="nome" required>

                <label for="sexo">Sexo:</label>
                <div class="radio-group">
                    <label for="masculino">
                        <input type="radio" id="masculino" name="sexo" value="M" required> Masculino
                    </label>
                    <label for="feminino">
                        <input type="radio" id="feminino" name="sexo" value="F" required> Feminino
                    </label>
                </div>

                <label for="fone">Telefone:</label>
                <input type="text" name="fone" required>

                <label for="email">Email:</label>
                <input type="email" name="email" required>

                <label for="senha">Senha:</label>
                <input type="password" name="senha" required>

                <input type="submit" value="Adicionar">
            </form>
        </div>
    </div>
</body>
</html>

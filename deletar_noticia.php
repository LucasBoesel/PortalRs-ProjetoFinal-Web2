<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
include_once './config/config.php';
include_once './classes/Noticia.php';  // aqui troca Usuario por Noticia

$noticia = new Noticia($db);  // instancia da classe Noticia

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $noticia->deletar($id);   // chama o deletar da noticia
    header('Location: perfil_noticia.php');
    exit();
}
?>
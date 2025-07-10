<?php
include 'conexao.php';

$anuncios = $pdo->query("SELECT * FROM anuncio ORDER BY data_cadastro DESC")->fetchAll();
?>

<h2>Anúncios Cadastrados</h2>
<table>
    <tr>
        <th>Imagem</th><th>Nome</th><th>Valor</th><th>Destaque</th><th>Status</th><th>Ações</th>
    </tr>
    <?php foreach ($anuncios as $a): ?>
    <tr>
        <td><img src="<?= $a['imagem'] ?>" width="100"></td>
        <td><?= $a['nome'] ?></td>
        <td>R$ <?= number_format($a['valorAnuncio'], 2, ',', '.') ?></td>
        <td><?= $a['destaque'] ? 'Sim' : 'Não' ?></td>
        <td><?= $a['ativo'] ? 'Ativo' : 'Inativo' ?></td>
        <td>
            <a href="editar_anuncio.php?id=<?= $a['id'] ?>">Editar</a> |
            <a href="excluir_anuncio.php?id=<?= $a['id'] ?>" onclick="return confirm('Excluir anúncio?')">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
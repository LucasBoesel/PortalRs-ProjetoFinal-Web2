<?php
class Anuncio
{
    private $conn;
    private $table_name = "anuncio";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function criar($nome, $imagem, $link, $texto, $ativo, $destaque, $valorAnuncio)
    {
        $query = "INSERT INTO " . $this->table_name . " 
            (nome, imagem, link, texto, ativo, destaque, valorAnuncio, data_cadastro) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$nome, $imagem, $link, $texto, $ativo, $destaque, $valorAnuncio]);
        return $stmt;
    }

    public function lerTodos()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY data_cadastro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function lerPorId($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $imagem, $link, $texto, $ativo, $destaque, $valorAnuncio)
    {
        $query = "UPDATE " . $this->table_name . " 
            SET nome = ?, imagem = ?, link = ?, texto = ?, ativo = ?, destaque = ?, valorAnuncio = ?
            WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$nome, $imagem, $link, $texto, $ativo, $destaque, $valorAnuncio, $id]);
        return $stmt;
    }

    public function deletar($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt;
    }

    public function listarAtivos()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ativo = 1 ORDER BY destaque DESC, data_cadastro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function listarDestaques()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ativo = 1 AND destaque = 1 ORDER BY data_cadastro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function selecionarAnuncioAleatorio()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ativo = 1 ORDER BY RAND() LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

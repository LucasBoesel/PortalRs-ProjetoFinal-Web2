<?php
class Noticia
{
    private $conn;
    private $table_name = "noticias";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Busca uma notícia pelo ID
    public function lerPorId($id)
    {
        $query = "SELECT n.id, n.titulo, n.noticia, n.data, n.imagem, n.local, u.nome AS autor_nome
            FROM " . $this->table_name . " n
            LEFT JOIN usuarios u ON n.autor = u.id
            WHERE n.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deletar($id)
{
    $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute([$id]);
}
}
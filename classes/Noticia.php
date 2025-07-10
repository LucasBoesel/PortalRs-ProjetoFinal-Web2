<?php
class Noticia
{
    private $conn;
    private $table_name = "noticias";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Busca uma notícia pelo ID, incluindo viewsa
    public function lerPorId($id)
    {
        $query = "SELECT n.id, n.titulo, n.noticia, n.data, n.imagem, n.local, n.views, u.nome AS autor_nome
            FROM " . $this->table_name . " n
            LEFT JOIN usuarios u ON n.autor = u.id
            WHERE n.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Incrementa views da notícia
    public function incrementarViews($id)
    {
        $query = "UPDATE noticias SET views = views + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Conta quantos likes a notícia tem
    public function contarLikes($noticia_id)
    {
        $query = "SELECT COUNT(*) FROM noticia_likes WHERE noticia_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$noticia_id]);
        return (int) $stmt->fetchColumn();
    }

    // Verifica se usuário já curtiu a notícia
    public function usuarioCurtiu($noticia_id, $usuario_id)
    {
        $query = "SELECT 1 FROM noticia_likes WHERE noticia_id = ? AND usuario_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$noticia_id, $usuario_id]);
        return $stmt->fetch() !== false;
    }

    // Adiciona like na notícia (se não existir)
    public function adicionarLike($noticia_id, $usuario_id)
    {
        try {
            $query = "INSERT INTO noticia_likes (noticia_id, usuario_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$noticia_id, $usuario_id]);
        } catch (PDOException $e) {
            // Pode ser que o like já exista (unique constraint), ignorar
            return false;
        }
    }

    public function removerLike($noticia_id, $usuario_id)
{
    $query = "DELETE FROM noticia_likes WHERE noticia_id = ? AND usuario_id = ?";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute([$noticia_id, $usuario_id]);
}

    // Método para deletar notícia (você já tem)
    public function deletar($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}

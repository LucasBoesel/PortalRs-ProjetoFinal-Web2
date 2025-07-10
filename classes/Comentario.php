<?php
class Comentario {
  private $conn;

  public function __construct($db) {
    $this->conn = $db;
  }

  public function inserir($noticia_id, $usuario_id, $comentario) {
    $sql = "INSERT INTO comentarios (noticia_id, usuario_id, comentario) VALUES (:noticia_id, :usuario_id, :comentario)";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([
      ':noticia_id' => $noticia_id,
      ':usuario_id' => $usuario_id,
      ':comentario' => $comentario
    ]);
  }

  public function listarPorNoticia($noticia_id) {
    $sql = "SELECT c.*, u.nome, u.foto_perfil
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.noticia_id = :noticia_id
            ORDER BY c.data DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':noticia_id' => $noticia_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function lerPorId($id) {
  $query = "SELECT * FROM comentarios WHERE id = :id LIMIT 1";
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function excluir($id) {
  $query = "DELETE FROM comentarios WHERE id = :id";
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  return $stmt->execute();
}
}


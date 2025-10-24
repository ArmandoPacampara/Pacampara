<?php
require_once 'config/db.php';

class RoleModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllRoles() {
        $query = "SELECT * FROM roles";
        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoleById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM roles WHERE role_ID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

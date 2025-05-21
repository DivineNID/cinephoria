<?php
namespace App\Services;

class AuditLogService {
    private $db;
    public function __construct() {
        $this->db = \App\Services\Database::getMysqlConnection();
    }
    public function log($userId, $action, $details = null) {
        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $details]);
    }
}
<?php
/**
 * ActivityLog Model
 */
class ActivityLog extends Model {

    public function log(int|null $userId, string $action, string $module, string $description = ''): void {
        $this->db->query('INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES (:user, :action, :module, :desc, :ip)');
        $this->db->bind(':user', $userId);
        $this->db->bind(':action', $action);
        $this->db->bind(':module', $module);
        $this->db->bind(':desc', $description);
        $this->db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $this->db->execute();
    }

    public function getRecent(int $limit = 10): array {
        $this->db->query("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT {$limit}");
        return $this->db->resultSet();
    }

    public function getByUser(int $userId, int $limit = 20): array {
        $this->db->query("SELECT * FROM activity_logs WHERE user_id = :id ORDER BY created_at DESC LIMIT {$limit}");
        $this->db->bind(':id', $userId);
        return $this->db->resultSet();
    }

    public function getAll(int $limit = 50): array {
        $this->db->query("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT {$limit}");
        return $this->db->resultSet();
    }
}

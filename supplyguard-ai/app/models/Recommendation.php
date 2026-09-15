<?php
/**
 * Recommendation Model
 */
class Recommendation extends Model {

    public function getAll(string $status = ''): array {
        $sql = 'SELECT r.*, s.shipment_code, d.title as disruption_title, f.vehicle_code
                FROM recommendations r
                LEFT JOIN shipments s ON r.shipment_id = s.id
                LEFT JOIN disruptions d ON r.disruption_id = d.id
                LEFT JOIN fleet_assets f ON r.fleet_asset_id = f.id';
        if ($status) {
            $sql .= ' WHERE r.status = :status';
        }
        $sql .= " ORDER BY CASE r.priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END, r.created_at DESC";
        
        $this->db->query($sql);
        if ($status) {
            $this->db->bind(':status', $status);
        }
        return $this->db->resultSet();
    }

    public function getById(int $id): mixed {
        $this->db->query('SELECT r.*, s.shipment_code, d.title as disruption_title, f.vehicle_code
                          FROM recommendations r
                          LEFT JOIN shipments s ON r.shipment_id = s.id
                          LEFT JOIN disruptions d ON r.disruption_id = d.id
                          LEFT JOIN fleet_assets f ON r.fleet_asset_id = f.id
                          WHERE r.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getByShipment(int $shipmentId): array {
        $this->db->query('SELECT r.*, d.title as disruption_title, f.vehicle_code
                          FROM recommendations r
                          LEFT JOIN disruptions d ON r.disruption_id = d.id
                          LEFT JOIN fleet_assets f ON r.fleet_asset_id = f.id
                          WHERE r.shipment_id = :id ORDER BY r.created_at DESC');
        $this->db->bind(':id', $shipmentId);
        return $this->db->resultSet();
    }

    public function getByDisruption(int $disruptionId): array {
        $this->db->query('SELECT r.*, s.shipment_code, f.vehicle_code
                          FROM recommendations r
                          LEFT JOIN shipments s ON r.shipment_id = s.id
                          LEFT JOIN fleet_assets f ON r.fleet_asset_id = f.id
                          WHERE r.disruption_id = :id ORDER BY r.created_at DESC');
        $this->db->bind(':id', $disruptionId);
        return $this->db->resultSet();
    }

    public function create(array $data): int|false {
        $this->db->query('INSERT INTO recommendations (shipment_id, disruption_id, fleet_asset_id, recommendation_type, recommendation_text, priority, estimated_delay_reduction, estimated_cost_saving, status) 
                          VALUES (:shipment, :disruption, :fleet, :type, :text, :priority, :delay, :cost, :status)');
        
        $this->db->bind(':shipment', $data['shipment_id'] ?? null);
        $this->db->bind(':disruption', $data['disruption_id'] ?? null);
        $this->db->bind(':fleet', $data['fleet_asset_id'] ?? null);
        $this->db->bind(':type', $data['recommendation_type']);
        $this->db->bind(':text', $data['recommendation_text']);
        $this->db->bind(':priority', $data['priority'] ?? 'medium');
        $this->db->bind(':delay', $data['estimated_delay_reduction'] ?? null);
        $this->db->bind(':cost', $data['estimated_cost_saving'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'pending');
        
        if ($this->db->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function updateStatus(int $id, string $status): bool {
        $this->db->query('UPDATE recommendations SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getPendingCount(): int {
        $this->db->query("SELECT COUNT(*) as total FROM recommendations WHERE status = 'pending'");
        $row = $this->db->single();
        return $row ? $row->total : 0;
    }

    public function getRecent(int $limit = 5): array {
        $this->db->query("SELECT r.*, s.shipment_code, d.title as disruption_title
                          FROM recommendations r
                          LEFT JOIN shipments s ON r.shipment_id = s.id
                          LEFT JOIN disruptions d ON r.disruption_id = d.id
                          ORDER BY r.created_at DESC LIMIT {$limit}");
        return $this->db->resultSet();
    }

    public function getTypeCounts(): array {
        $this->db->query('SELECT recommendation_type, COUNT(*) as count FROM recommendations GROUP BY recommendation_type');
        return $this->db->resultSet();
    }
}

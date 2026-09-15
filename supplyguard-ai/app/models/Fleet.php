<?php
/**
 * Fleet Model
 */
class Fleet extends Model {

    public function getAll(string $status = ''): array {
        $sql = 'SELECT * FROM fleet_assets';
        if ($status) {
            $sql .= ' WHERE status = :status';
        }
        $sql .= ' ORDER BY vehicle_code';
        
        $this->db->query($sql);
        if ($status) {
            $this->db->bind(':status', $status);
        }
        return $this->db->resultSet();
    }

    public function getById(int $id): mixed {
        $this->db->query('SELECT * FROM fleet_assets WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getByCode(string $code): mixed {
        $this->db->query('SELECT * FROM fleet_assets WHERE vehicle_code = :code');
        $this->db->bind(':code', $code);
        return $this->db->single();
    }

    public function getAvailable(): array {
        $this->db->query("SELECT * FROM fleet_assets WHERE status IN ('available', 'idle') ORDER BY vehicle_code");
        return $this->db->resultSet();
    }

    public function getIdle(): array {
        $this->db->query("SELECT * FROM fleet_assets WHERE status = 'idle' ORDER BY vehicle_code");
        return $this->db->resultSet();
    }

    public function getTemperatureCapable(): array {
        $this->db->query("SELECT * FROM fleet_assets WHERE temperature_capable = 1 AND status IN ('available', 'idle') ORDER BY vehicle_code");
        return $this->db->resultSet();
    }

    public function getStatusCounts(): object {
        $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = 'in_transit' THEN 1 ELSE 0 END) as in_transit,
            SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
            SUM(CASE WHEN status = 'idle' THEN 1 ELSE 0 END) as idle,
            SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved
            FROM fleet_assets");
        return $this->db->single();
    }

    public function getAvgUtilization(): float {
        $this->db->query("SELECT AVG(utilization_percentage) as avg_util FROM fleet_assets WHERE status = 'in_transit'");
        $row = $this->db->single();
        return $row ? round((float)$row->avg_util, 1) : 0;
    }

    public function getByLocation(string $location): array {
        $this->db->query("SELECT * FROM fleet_assets WHERE current_location LIKE :loc AND status IN ('available', 'idle')");
        $this->db->bind(':loc', '%' . $location . '%');
        return $this->db->resultSet();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['current_location', 'status', 'utilization_percentage', 'fuel_level'];
        
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        
        $sql = 'UPDATE fleet_assets SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->execute();
    }
}

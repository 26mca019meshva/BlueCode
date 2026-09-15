<?php
/**
 * Shipment Model
 */
class Shipment extends Model {

    public function getAll(array $filters = [], int $limit = 0, int $offset = 0): array {
        $sql = 'SELECT s.*, f.vehicle_code FROM shipments s LEFT JOIN fleet_assets f ON s.vehicle_id = f.id WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND s.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['risk_level'])) {
            $sql .= ' AND s.risk_level = :risk_level';
            $params[':risk_level'] = $filters['risk_level'];
        }
        if (!empty($filters['cargo_type'])) {
            $sql .= ' AND s.cargo_type = :cargo_type';
            $params[':cargo_type'] = $filters['cargo_type'];
        }
        if (!empty($filters['priority'])) {
            $sql .= ' AND s.priority = :priority';
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (s.shipment_code LIKE :search OR s.origin LIKE :search2 OR s.destination LIKE :search3 OR s.cargo_description LIKE :search4)';
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
            $params[':search3'] = '%' . $filters['search'] . '%';
            $params[':search4'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['cold_chain'])) {
            $sql .= ' AND s.cold_chain_required = 1';
        }

        $sql .= " ORDER BY CASE s.risk_level WHEN 'CRITICAL' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 WHEN 'LOW' THEN 4 ELSE 5 END, s.updated_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->resultSet();
    }

    public function getById(int $id): mixed {
        $this->db->query('SELECT s.*, f.vehicle_code, f.vehicle_type as vehicle_type_name, f.carrier as vehicle_carrier 
                          FROM shipments s 
                          LEFT JOIN fleet_assets f ON s.vehicle_id = f.id 
                          WHERE s.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getByCode(string $code): mixed {
        $this->db->query('SELECT s.*, f.vehicle_code FROM shipments s LEFT JOIN fleet_assets f ON s.vehicle_id = f.id WHERE s.shipment_code = :code');
        $this->db->bind(':code', $code);
        return $this->db->single();
    }

    public function create(array $data): int|false {
        $this->db->query('INSERT INTO shipments (shipment_code, origin, destination, current_location, cargo_type, cargo_description, cargo_value, weight_kg, priority, carrier, vehicle_id, status, eta, delivery_deadline, cold_chain_required, temp_min, temp_max, risk_score, risk_level, route_via) 
                          VALUES (:code, :origin, :dest, :current, :cargo_type, :cargo_desc, :cargo_val, :weight, :priority, :carrier, :vehicle, :status, :eta, :deadline, :cold_chain, :temp_min, :temp_max, :risk_score, :risk_level, :route_via)');
        
        $this->db->bind(':code', $data['shipment_code']);
        $this->db->bind(':origin', $data['origin']);
        $this->db->bind(':dest', $data['destination']);
        $this->db->bind(':current', $data['current_location']);
        $this->db->bind(':cargo_type', $data['cargo_type']);
        $this->db->bind(':cargo_desc', $data['cargo_description'] ?? '');
        $this->db->bind(':cargo_val', $data['cargo_value']);
        $this->db->bind(':weight', $data['weight_kg'] ?? 0);
        $this->db->bind(':priority', $data['priority']);
        $this->db->bind(':carrier', $data['carrier'] ?? null);
        $this->db->bind(':vehicle', $data['vehicle_id'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':eta', $data['eta'] ?? null);
        $this->db->bind(':deadline', $data['delivery_deadline'] ?? null);
        $this->db->bind(':cold_chain', $data['cold_chain_required'] ?? 0);
        $this->db->bind(':temp_min', $data['temp_min'] ?? null);
        $this->db->bind(':temp_max', $data['temp_max'] ?? null);
        $this->db->bind(':risk_score', $data['risk_score'] ?? 0);
        $this->db->bind(':risk_level', $data['risk_level'] ?? 'LOW');
        $this->db->bind(':route_via', $data['route_via'] ?? null);
        
        if ($this->db->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        
        $allowed = ['origin', 'destination', 'current_location', 'cargo_type', 'cargo_description', 'cargo_value', 'weight_kg', 'priority', 'carrier', 'vehicle_id', 'status', 'eta', 'delivery_deadline', 'cold_chain_required', 'temp_min', 'temp_max', 'risk_score', 'risk_level', 'route_via'];
        
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $sql = 'UPDATE shipments SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->execute();
    }

    public function delete(int $id): bool {
        $this->db->query('DELETE FROM shipments WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getCount(array $filters = []): int {
        $sql = 'SELECT COUNT(*) as total FROM shipments WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['risk_level'])) {
            $sql .= ' AND risk_level = :risk_level';
            $params[':risk_level'] = $filters['risk_level'];
        }

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $row = $this->db->single();
        return $row ? $row->total : 0;
    }

    public function getHighRisk(): array {
        $this->db->query('SELECT s.*, f.vehicle_code FROM shipments s LEFT JOIN fleet_assets f ON s.vehicle_id = f.id WHERE s.risk_level IN ("HIGH", "CRITICAL") ORDER BY s.risk_score DESC');
        return $this->db->resultSet();
    }

    public function getColdChainShipments(): array {
        $this->db->query('SELECT s.*, f.vehicle_code FROM shipments s LEFT JOIN fleet_assets f ON s.vehicle_id = f.id WHERE s.cold_chain_required = 1 ORDER BY s.risk_score DESC');
        return $this->db->resultSet();
    }

    public function getByLocation(string $location): array {
        $this->db->query('SELECT * FROM shipments WHERE current_location LIKE :loc OR route_via LIKE :route');
        $this->db->bind(':loc', '%' . $location . '%');
        $this->db->bind(':route', '%' . $location . '%');
        return $this->db->resultSet();
    }

    public function getAffectedByDisruption(string $location): array {
        $this->db->query("SELECT s.*, f.vehicle_code FROM shipments s 
                          LEFT JOIN fleet_assets f ON s.vehicle_id = f.id 
                          WHERE s.status NOT IN ('delivered', 'cancelled') 
                          AND (s.current_location LIKE :loc1 OR s.route_via LIKE :loc2 OR s.origin LIKE :loc3 OR s.destination LIKE :loc4) 
                          ORDER BY s.risk_score DESC");
        $this->db->bind(':loc1', '%' . $location . '%');
        $this->db->bind(':loc2', '%' . $location . '%');
        $this->db->bind(':loc3', '%' . $location . '%');
        $this->db->bind(':loc4', '%' . $location . '%');
        return $this->db->resultSet();
    }

    public function getRiskDistribution(): array {
        $this->db->query("SELECT risk_level, COUNT(*) as count FROM shipments WHERE status NOT IN ('delivered', 'cancelled') GROUP BY risk_level");
        return $this->db->resultSet();
    }

    public function getStatusDistribution(): array {
        $this->db->query('SELECT status, COUNT(*) as count FROM shipments GROUP BY status');
        return $this->db->resultSet();
    }

    public function getTopAtRisk(int $limit = 5): array {
        $this->db->query("SELECT s.*, f.vehicle_code FROM shipments s LEFT JOIN fleet_assets f ON s.vehicle_id = f.id WHERE s.status NOT IN ('delivered', 'cancelled') ORDER BY s.risk_score DESC LIMIT {$limit}");
        return $this->db->resultSet();
    }

    public function getTotalCargoValueAtRisk(): float {
        $this->db->query("SELECT SUM(cargo_value) as total FROM shipments WHERE risk_level IN ('HIGH', 'CRITICAL') AND status NOT IN ('delivered', 'cancelled')");
        $row = $this->db->single();
        return $row ? (float)$row->total : 0;
    }
}

<?php
/**
 * Disruption Model
 */
class Disruption extends Model {

    public function getAll(string $status = ''): array {
        $sql = 'SELECT * FROM disruptions';
        if ($status) {
            $sql .= ' WHERE status = :status';
        }
        $sql .= " ORDER BY CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 WHEN 'LOW' THEN 4 ELSE 5 END, start_time DESC";
        
        $this->db->query($sql);
        if ($status) {
            $this->db->bind(':status', $status);
        }
        return $this->db->resultSet();
    }

    public function getActive(): array {
        return $this->getAll('active');
    }

    public function getById(int $id): mixed {
        $this->db->query('SELECT * FROM disruptions WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create(array $data): int|false {
        $this->db->query('INSERT INTO disruptions (title, type, location, severity, description, affected_routes, radius_km, start_time, estimated_end_time, status) 
                          VALUES (:title, :type, :location, :severity, :description, :routes, :radius, :start, :end, :status)');
        
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':location', $data['location']);
        $this->db->bind(':severity', $data['severity']);
        $this->db->bind(':description', $data['description'] ?? '');
        $this->db->bind(':routes', $data['affected_routes'] ?? '');
        $this->db->bind(':radius', $data['radius_km'] ?? 50);
        $this->db->bind(':start', $data['start_time']);
        $this->db->bind(':end', $data['estimated_end_time'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');
        
        if ($this->db->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['title', 'type', 'location', 'severity', 'description', 'affected_routes', 'radius_km', 'start_time', 'estimated_end_time', 'status'];
        
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $sql = 'UPDATE disruptions SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->execute();
    }

    public function getActiveCount(): int {
        $this->db->query("SELECT COUNT(*) as total FROM disruptions WHERE status = 'active'");
        $row = $this->db->single();
        return $row ? $row->total : 0;
    }

    public function getByType(): array {
        $this->db->query("SELECT type, COUNT(*) as count FROM disruptions WHERE status = 'active' GROUP BY type");
        return $this->db->resultSet();
    }
}

<?php
/**
 * SensorReading Model
 */
class SensorReading extends Model {

    public function getByShipment(int $shipmentId): array {
        $this->db->query('SELECT * FROM sensor_readings WHERE shipment_id = :id ORDER BY recorded_at ASC');
        $this->db->bind(':id', $shipmentId);
        return $this->db->resultSet();
    }

    public function getLatest(int $shipmentId): mixed {
        $this->db->query('SELECT * FROM sensor_readings WHERE shipment_id = :id ORDER BY recorded_at DESC LIMIT 1');
        $this->db->bind(':id', $shipmentId);
        return $this->db->single();
    }

    public function getHistory(int $shipmentId, int $limit = 50): array {
        $this->db->query("SELECT * FROM sensor_readings WHERE shipment_id = :id ORDER BY recorded_at DESC LIMIT {$limit}");
        $this->db->bind(':id', $shipmentId);
        return $this->db->resultSet();
    }

    public function getExcursions(float $minTemp, float $maxTemp): array {
        $this->db->query('SELECT sr.*, s.shipment_code, s.cargo_type, s.temp_min, s.temp_max 
                          FROM sensor_readings sr 
                          JOIN shipments s ON sr.shipment_id = s.id 
                          WHERE s.cold_chain_required = 1 
                          AND (sr.temperature < s.temp_min OR sr.temperature > s.temp_max)
                          ORDER BY sr.recorded_at DESC');
        return $this->db->resultSet();
    }

    public function getLatestForAllColdChain(): array {
        $this->db->query('SELECT sr.*, s.shipment_code, s.cargo_type, s.cargo_description, s.temp_min, s.temp_max, s.origin, s.destination, s.current_location, s.status as shipment_status, s.priority
                          FROM sensor_readings sr
                          INNER JOIN (
                              SELECT shipment_id, MAX(recorded_at) as max_recorded
                              FROM sensor_readings
                              GROUP BY shipment_id
                          ) latest ON sr.shipment_id = latest.shipment_id AND sr.recorded_at = latest.max_recorded
                          JOIN shipments s ON sr.shipment_id = s.id
                          WHERE s.cold_chain_required = 1
                          ORDER BY ABS(sr.temperature - (s.temp_max + s.temp_min) / 2) DESC');
        return $this->db->resultSet();
    }

    public function getColdChainAlertCount(): int {
        $this->db->query('SELECT COUNT(DISTINCT sr.shipment_id) as total
                          FROM sensor_readings sr
                          INNER JOIN (
                              SELECT shipment_id, MAX(recorded_at) as max_recorded
                              FROM sensor_readings
                              GROUP BY shipment_id
                          ) latest ON sr.shipment_id = latest.shipment_id AND sr.recorded_at = latest.max_recorded
                          JOIN shipments s ON sr.shipment_id = s.id
                          WHERE s.cold_chain_required = 1
                          AND (sr.temperature < s.temp_min OR sr.temperature > s.temp_max)');
        $row = $this->db->single();
        return $row ? $row->total : 0;
    }
}

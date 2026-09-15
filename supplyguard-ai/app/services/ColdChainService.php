<?php
/**
 * Cold Chain Service
 * Monitors cold-chain shipments, detects temperature excursions, and classifies severity.
 */
class ColdChainService {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get monitoring dashboard data
     */
    public function getMonitoringDashboard(): array {
        $this->db->query("SELECT s.*, 
                          (SELECT sr.temperature FROM sensor_readings sr WHERE sr.shipment_id = s.id ORDER BY sr.recorded_at DESC LIMIT 1) as latest_temp,
                          (SELECT sr.humidity FROM sensor_readings sr WHERE sr.shipment_id = s.id ORDER BY sr.recorded_at DESC LIMIT 1) as latest_humidity,
                          (SELECT sr.recorded_at FROM sensor_readings sr WHERE sr.shipment_id = s.id ORDER BY sr.recorded_at DESC LIMIT 1) as last_reading_at
                          FROM shipments s 
                          WHERE s.cold_chain_required = 1 
                          AND s.status NOT IN ('delivered', 'cancelled')
                          ORDER BY s.risk_score DESC");
        $shipments = $this->db->resultSet();

        $alertCount = 0;
        $normalCount = 0;

        foreach ($shipments as &$s) {
            $s->severity = $this->classifySeverityFromValues((float)($s->latest_temp ?? 0), (float)$s->temp_min, (float)$s->temp_max);
            if ($s->severity !== 'NORMAL') {
                $alertCount++;
            } else {
                $normalCount++;
            }
        }

        return [
            'shipments' => $shipments,
            'alertCount' => $alertCount,
            'normalCount' => $normalCount
        ];
    }

    /**
     * Classify severity based on latest reading and shipment thresholds
     */
    public function classifySeverity(?object $reading, object $shipment): string {
        if (!$reading) return 'NORMAL';
        return $this->classifySeverityFromValues((float)$reading->temperature, (float)$shipment->temp_min, (float)$shipment->temp_max);
    }

    /**
     * Classify severity from temperature values
     */
    private function classifySeverityFromValues(float $temp, float $min, float $max): string {
        if ($temp > $max + 3 || $temp < $min - 3) return SEVERITY_CRITICAL;
        if ($temp > $max + 1 || $temp < $min - 1) return SEVERITY_HIGH;
        if ($temp > $max || $temp < $min) return SEVERITY_WARNING;
        if ($temp > $max - 1 || $temp < $min + 1) return SEVERITY_WARNING; // approaching threshold
        return SEVERITY_NORMAL;
    }

    /**
     * Get actionable recommendation based on severity
     */
    public function getRecommendation(string $severity, object $shipment): string {
        return match($severity) {
            SEVERITY_CRITICAL => "URGENT: {$shipment->shipment_code} temperature has significantly exceeded safe limits. Immediately halt transit and transfer cargo to emergency cold storage. {$shipment->cargo_type} cargo may be compromised. Contact carrier and notify customer immediately.",
            SEVERITY_HIGH => "HIGH ALERT: {$shipment->shipment_code} temperature is outside the acceptable range. Transfer cargo to a refrigerated vehicle immediately. If no refrigerated vehicle is available nearby, identify the nearest cold storage facility. Estimated time to cargo compromise: 1-2 hours.",
            SEVERITY_WARNING => "WARNING: {$shipment->shipment_code} temperature is approaching the threshold. Monitor closely and prepare backup refrigeration. If temperature continues to rise, initiate cargo transfer within the next 30 minutes.",
            default => "{$shipment->shipment_code} temperature is within the normal range. Continue monitoring. No action required."
        };
    }

    /**
     * Get all cold-chain alerts
     */
    public function getAlerts(): array {
        $dashboard = $this->getMonitoringDashboard();
        $alerts = [];
        foreach ($dashboard['shipments'] as $s) {
            if ($s->severity !== 'NORMAL') {
                $alerts[] = [
                    'shipment_id' => $s->id,
                    'shipment_code' => $s->shipment_code,
                    'cargo_type' => $s->cargo_type,
                    'current_temp' => (float)$s->latest_temp,
                    'temp_min' => (float)$s->temp_min,
                    'temp_max' => (float)$s->temp_max,
                    'severity' => $s->severity,
                    'recommendation' => $this->getRecommendation($s->severity, $s)
                ];
            }
        }
        return $alerts;
    }
}

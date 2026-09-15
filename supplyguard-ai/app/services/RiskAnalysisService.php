<?php
/**
 * Risk Analysis Service
 * Multi-factor risk scoring engine for shipments.
 */
class RiskAnalysisService {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Analyze risk for a shipment
     */
    public function analyzeShipment(object $shipment): array {
        $score = 0;
        $reasons = [];

        // 1. Check active disruptions on route (0-30 points)
        $disruptionScore = $this->checkDisruptionExposure($shipment);
        $score += $disruptionScore['score'];
        if (!empty($disruptionScore['reasons'])) {
            $reasons = array_merge($reasons, $disruptionScore['reasons']);
        }

        // 2. Delivery deadline urgency (0-20 points)
        if ($shipment->delivery_deadline) {
            $hoursLeft = (strtotime($shipment->delivery_deadline) - time()) / 3600;
            if ($hoursLeft < 0) {
                $score += 20;
                $reasons[] = 'Delivery deadline has passed — shipment is overdue';
            } elseif ($hoursLeft < 6) {
                $score += 18;
                $reasons[] = 'Delivery deadline within ' . round($hoursLeft, 1) . ' hours — critical urgency';
            } elseif ($hoursLeft < 12) {
                $score += 14;
                $reasons[] = 'Delivery deadline within 12 hours';
            } elseif ($hoursLeft < 24) {
                $score += 8;
                $reasons[] = 'Delivery deadline within 24 hours';
            }
        }

        // 3. Cargo value (0-15 points)
        $value = (float)$shipment->cargo_value;
        if ($value >= 5000000) {
            $score += 15;
            $reasons[] = 'High-value cargo (₹' . number_format($value / 100000, 1) . ' Lakh)';
        } elseif ($value >= 2000000) {
            $score += 10;
            $reasons[] = 'Significant cargo value (₹' . number_format($value / 100000, 1) . ' Lakh)';
        } elseif ($value >= 500000) {
            $score += 5;
        }

        // 4. Priority escalation (0-10 points)
        $priorityScores = ['critical' => 10, 'high' => 7, 'medium' => 3, 'low' => 0];
        $priorityScore = $priorityScores[$shipment->priority] ?? 0;
        $score += $priorityScore;
        if ($shipment->priority === 'critical') {
            $reasons[] = 'Critical priority shipment';
        }

        // 5. Current status (0-10 points)
        $statusScores = ['at_risk' => 10, 'delayed' => 8, 'in_transit' => 2, 'pending' => 1, 'delivered' => 0, 'cancelled' => 0];
        $score += $statusScores[$shipment->status] ?? 0;
        if ($shipment->status === 'delayed') {
            $reasons[] = 'Shipment is currently delayed';
        } elseif ($shipment->status === 'at_risk') {
            $reasons[] = 'Shipment is flagged as at-risk';
        }

        // 6. Cold-chain temperature excursion (0-15 points)
        if ($shipment->cold_chain_required) {
            $tempScore = $this->checkTemperatureExcursion($shipment);
            $score += $tempScore['score'];
            if (!empty($tempScore['reasons'])) {
                $reasons = array_merge($reasons, $tempScore['reasons']);
            }
        }

        // Cap at 100
        $score = min(100, $score);

        // Determine risk level
        $level = match(true) {
            $score >= RISK_THRESHOLD_HIGH => RISK_CRITICAL,
            $score >= RISK_THRESHOLD_MEDIUM => RISK_HIGH,
            $score >= RISK_THRESHOLD_LOW => RISK_MEDIUM,
            default => RISK_LOW,
        };

        return [
            'risk_score' => $score,
            'risk_level' => $level,
            'risk_reasons' => $reasons,
            'shipment_code' => $shipment->shipment_code,
            'cargo_type' => $shipment->cargo_type
        ];
    }

    /**
     * Check if shipment route passes through active disruption zones
     */
    private function checkDisruptionExposure(object $shipment): array {
        $this->db->query("SELECT * FROM disruptions WHERE status = 'active'");
        $disruptions = $this->db->resultSet();

        $score = 0;
        $reasons = [];
        $routeVia = $shipment->route_via ?? '';

        foreach ($disruptions as $d) {
            $location = strtolower($d->location);
            $currentLoc = strtolower($shipment->current_location);
            $route = strtolower($routeVia);

            if (str_contains($currentLoc, $location) || str_contains($route, $location)) {
                $severityScores = ['CRITICAL' => 30, 'HIGH' => 25, 'MEDIUM' => 15, 'LOW' => 8];
                $disruptionScore = $severityScores[$d->severity] ?? 10;
                $score = max($score, $disruptionScore);

                $typeLabels = [
                    'weather' => 'Weather disruption',
                    'road_closure' => 'Road closure',
                    'port_congestion' => 'Port congestion',
                    'vehicle_breakdown' => 'Vehicle breakdown',
                    'supply_disruption' => 'Supply chain disruption'
                ];
                $typeLabel = $typeLabels[$d->type] ?? 'Disruption';
                $reasons[] = "{$typeLabel} ({$d->severity}) near {$d->location}: {$d->title}";
            }
        }

        return ['score' => $score, 'reasons' => $reasons];
    }

    /**
     * Check cold-chain temperature status
     */
    private function checkTemperatureExcursion(object $shipment): array {
        $this->db->query('SELECT * FROM sensor_readings WHERE shipment_id = :id ORDER BY recorded_at DESC LIMIT 1');
        $this->db->bind(':id', $shipment->id);
        $latest = $this->db->single();

        if (!$latest) return ['score' => 0, 'reasons' => []];

        $temp = (float)$latest->temperature;
        $min = (float)$shipment->temp_min;
        $max = (float)$shipment->temp_max;

        if ($temp > $max) {
            $deviation = $temp - $max;
            $score = min(15, (int)($deviation * 5));
            return [
                'score' => $score,
                'reasons' => ["Temperature excursion: {$temp}°C exceeds maximum {$max}°C (deviation: +{$deviation}°C)"]
            ];
        } elseif ($temp < $min) {
            $deviation = $min - $temp;
            $score = min(15, (int)($deviation * 5));
            return [
                'score' => $score,
                'reasons' => ["Temperature excursion: {$temp}°C below minimum {$min}°C (deviation: -{$deviation}°C)"]
            ];
        } elseif ($temp > ($max - COLD_CHAIN_WARNING_MARGIN)) {
            return [
                'score' => 5,
                'reasons' => ["Temperature approaching upper limit: {$temp}°C (max: {$max}°C)"]
            ];
        }

        return ['score' => 0, 'reasons' => []];
    }
}

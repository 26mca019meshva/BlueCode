<?php
/**
 * Fleet Optimization Service
 * Identifies idle fleet assets suitable for redeployment to affected shipments.
 */
class FleetOptimizationService {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find redeployment opportunities matching idle vehicles to high-risk shipments
     */
    public function findRedeploymentOptions(): array {
        // Get idle/available vehicles
        $this->db->query("SELECT * FROM fleet_assets WHERE status IN ('idle', 'available') ORDER BY vehicle_code");
        $idleVehicles = $this->db->resultSet();

        // Get high-risk shipments needing help
        $this->db->query("SELECT * FROM shipments WHERE risk_level IN ('HIGH', 'CRITICAL') AND status NOT IN ('delivered', 'cancelled') ORDER BY risk_score DESC");
        $atRiskShipments = $this->db->resultSet();

        $redeployments = [];

        foreach ($idleVehicles as $vehicle) {
            foreach ($atRiskShipments as $shipment) {
                $distance = $this->estimateDistance($vehicle->current_location, $shipment->current_location);
                $isSuitable = $this->checkSuitability($vehicle, $shipment);

                if ($isSuitable && $distance <= 200) {
                    $redeployments[] = [
                        'vehicle' => [
                            'id' => $vehicle->id,
                            'code' => $vehicle->vehicle_code,
                            'type' => $vehicle->vehicle_type,
                            'carrier' => $vehicle->carrier,
                            'location' => $vehicle->current_location,
                            'capacity' => $vehicle->capacity_tons,
                            'temperature_capable' => (bool)$vehicle->temperature_capable,
                            'fuel_level' => $vehicle->fuel_level,
                            'status' => $vehicle->status
                        ],
                        'shipment' => [
                            'id' => $shipment->id,
                            'code' => $shipment->shipment_code,
                            'origin' => $shipment->origin,
                            'destination' => $shipment->destination,
                            'current_location' => $shipment->current_location,
                            'cargo_type' => $shipment->cargo_type,
                            'risk_level' => $shipment->risk_level,
                            'risk_score' => $shipment->risk_score,
                            'cold_chain_required' => (bool)$shipment->cold_chain_required,
                            'priority' => $shipment->priority
                        ],
                        'estimated_distance_km' => $distance,
                        'suitability_score' => $this->calculateSuitabilityScore($vehicle, $shipment, $distance),
                        'recommendation' => $this->generateRecommendation($vehicle, $shipment, $distance)
                    ];
                }
            }
        }

        // Sort by suitability score (highest first)
        usort($redeployments, fn($a, $b) => $b['suitability_score'] - $a['suitability_score']);

        return $redeployments;
    }

    /**
     * Estimate distance between two locations (simulated for prototype)
     */
    private function estimateDistance(string $from, string $to): int {
        $distances = [
            'Mumbai-Vadodara' => 18, 'Vadodara-Mumbai' => 18,
            'Mumbai-Pune' => 30, 'Pune-Mumbai' => 30,
            'Mumbai-Surat' => 80, 'Surat-Mumbai' => 80,
            'Delhi-Jaipur' => 50, 'Jaipur-Delhi' => 50,
            'Chennai-Bengaluru' => 100, 'Bengaluru-Chennai' => 100,
            'Hyderabad-Bengaluru' => 120, 'Bengaluru-Hyderabad' => 120,
            'Ahmedabad-Vadodara' => 25, 'Vadodara-Ahmedabad' => 25,
            'Vadodara-Surat' => 60, 'Surat-Vadodara' => 60,
            'Delhi-Near Jaipur' => 40, 'Pune-Lonavala' => 20,
            'Vadodara-Near Vadodara' => 18,
        ];

        $key = $from . '-' . $to;
        $keyReverse = $to . '-' . $from;

        // Check direct match
        if (isset($distances[$key])) return $distances[$key];
        if (isset($distances[$keyReverse])) return $distances[$keyReverse];

        // Check partial match
        foreach ($distances as $k => $v) {
            $parts = explode('-', $k);
            if ((str_contains($from, $parts[0]) || str_contains($parts[0], $from)) &&
                (str_contains($to, $parts[1]) || str_contains($parts[1], $to))) {
                return $v;
            }
        }

        // Same city
        if (strtolower($from) === strtolower($to)) return 5;

        // Default: simulate a moderate distance
        return rand(50, 150);
    }

    /**
     * Check if vehicle is suitable for shipment
     */
    private function checkSuitability(object $vehicle, object $shipment): bool {
        // Cold chain requirement
        if ($shipment->cold_chain_required && !$vehicle->temperature_capable) {
            return false;
        }
        // Capacity check (basic)
        if ($shipment->weight_kg > 0 && ($vehicle->capacity_tons * 1000) < $shipment->weight_kg) {
            return false;
        }
        return true;
    }

    /**
     * Calculate suitability score (0-100)
     */
    private function calculateSuitabilityScore(object $vehicle, object $shipment, int $distance): int {
        $score = 100;

        // Distance penalty
        $score -= min(30, $distance / 5);

        // Cold chain bonus
        if ($shipment->cold_chain_required && $vehicle->temperature_capable) {
            $score += 15;
        }

        // Fuel level
        if ($vehicle->fuel_level < 30) $score -= 20;

        // Shipment priority bonus
        $priorityBonus = ['critical' => 10, 'high' => 5, 'medium' => 0, 'low' => -5];
        $score += $priorityBonus[$shipment->priority] ?? 0;

        return max(0, min(100, (int)$score));
    }

    /**
     * Generate redeployment recommendation text
     */
    private function generateRecommendation(object $vehicle, object $shipment, int $distance): string {
        $rec = "Redeploy {$vehicle->vehicle_code} ({$vehicle->vehicle_type}) ";
        $rec .= "from {$vehicle->current_location} to support {$shipment->shipment_code}. ";
        $rec .= "Estimated distance: {$distance} km. ";

        if ($vehicle->temperature_capable && $shipment->cold_chain_required) {
            $rec .= "Vehicle is temperature-capable — suitable for cold-chain cargo. ";
        }

        $rec .= "Vehicle fuel level: {$vehicle->fuel_level}%.";
        return $rec;
    }
}

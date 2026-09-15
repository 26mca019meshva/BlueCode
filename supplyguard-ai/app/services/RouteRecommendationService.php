<?php
/**
 * Route Recommendation Service
 * Provides alternative route suggestions based on disruptions.
 * Uses pre-configured route data for the prototype (simulated intelligence).
 */
class RouteRecommendationService {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get alternative route for a shipment affected by disruption
     */
    public function getAlternativeRoute(object $shipment, ?object $disruption = null): ?array {
        // Get primary route
        $this->db->query("SELECT * FROM routes WHERE origin = :origin AND destination = :dest AND is_primary = 1 LIMIT 1");
        $this->db->bind(':origin', $shipment->origin);
        $this->db->bind(':dest', $shipment->destination);
        $primaryRoute = $this->db->single();

        // Get alternative routes
        $this->db->query("SELECT * FROM routes WHERE origin = :origin AND destination = :dest AND is_primary = 0 AND status = 'active' ORDER BY estimated_hours ASC");
        $this->db->bind(':origin', $shipment->origin);
        $this->db->bind(':dest', $shipment->destination);
        $alternatives = $this->db->resultSet();

        if (empty($alternatives)) {
            return null;
        }

        $altRoute = $alternatives[0];
        $currentVia = $shipment->route_via ? explode(',', $shipment->route_via) : [];
        $altVia = explode(',', $altRoute->via_points);

        $result = [
            'current_route' => [
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'via' => $currentVia,
                'distance_km' => $primaryRoute ? $primaryRoute->distance_km : 'N/A',
                'estimated_hours' => $primaryRoute ? $primaryRoute->estimated_hours : 'N/A',
                'status' => $primaryRoute ? $primaryRoute->status : 'unknown',
                'risk_level' => $primaryRoute ? $primaryRoute->risk_level : 'UNKNOWN'
            ],
            'recommended_route' => [
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'via' => $altVia,
                'distance_km' => $altRoute->distance_km,
                'estimated_hours' => $altRoute->estimated_hours,
                'status' => $altRoute->status,
                'risk_level' => $altRoute->risk_level
            ],
            'savings' => [
                'distance_diff' => $primaryRoute ? ($altRoute->distance_km - $primaryRoute->distance_km) : 0,
                'time_diff' => $primaryRoute ? ($altRoute->estimated_hours - $primaryRoute->estimated_hours) : 0,
            ],
            'reason' => $this->generateReason($shipment, $disruption, $altRoute)
        ];

        return $result;
    }

    /**
     * Generate human-readable recommendation reason
     */
    private function generateReason(object $shipment, ?object $disruption, object $altRoute): string {
        $reason = "Reroute {$shipment->shipment_code} via {$altRoute->via_points} ";
        
        if ($disruption) {
            $reason .= "to avoid {$disruption->type} disruption near {$disruption->location}. ";
        }
        
        $reason .= "Alternative route is {$altRoute->distance_km} km ({$altRoute->estimated_hours} hours). ";
        $reason .= "Route risk level: {$altRoute->risk_level}.";
        
        return $reason;
    }
}

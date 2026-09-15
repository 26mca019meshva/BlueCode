<?php
/**
 * Disruption Analysis Service
 * Calculates impact of disruptions on shipments and cargo.
 */
class DisruptionAnalysisService {

    /**
     * Analyze the full impact of a disruption
     */
    public function analyzeImpact(object $disruption, array $affectedShipments): array {
        $totalAffected = count($affectedShipments);
        $highRisk = 0;
        $criticalRisk = 0;
        $totalCargoValue = 0;

        foreach ($affectedShipments as $s) {
            $totalCargoValue += (float)$s->cargo_value;
            if ($s->risk_level === 'HIGH') $highRisk++;
            if ($s->risk_level === 'CRITICAL') $criticalRisk++;
        }

        // Estimate delay based on disruption type and severity
        $delayEstimate = $this->estimateDelay($disruption);

        // Determine overall risk
        $overallRisk = 'LOW';
        if ($criticalRisk > 0 || $totalCargoValue > 5000000) $overallRisk = 'CRITICAL';
        elseif ($highRisk > 2 || $totalCargoValue > 2000000) $overallRisk = 'HIGH';
        elseif ($totalAffected > 3) $overallRisk = 'MEDIUM';

        // Generate actionable summary
        $actions = $this->generateActions($disruption, $affectedShipments);

        return [
            'disruption_id' => $disruption->id,
            'disruption_title' => $disruption->title,
            'total_affected' => $totalAffected,
            'high_risk' => $highRisk,
            'critical_risk' => $criticalRisk,
            'cargo_value_at_risk' => $totalCargoValue,
            'cargo_value_formatted' => $this->formatCurrency($totalCargoValue),
            'estimated_delay' => $delayEstimate,
            'overall_risk' => $overallRisk,
            'actions' => $actions,
            'affected_shipments' => array_map(function($s) {
                return [
                    'id' => $s->id,
                    'code' => $s->shipment_code,
                    'origin' => $s->origin,
                    'destination' => $s->destination,
                    'cargo_type' => $s->cargo_type,
                    'cargo_value' => (float)$s->cargo_value,
                    'risk_level' => $s->risk_level,
                    'risk_score' => $s->risk_score,
                    'status' => $s->status,
                    'priority' => $s->priority
                ];
            }, $affectedShipments)
        ];
    }

    /**
     * Estimate delay based on disruption characteristics
     */
    private function estimateDelay(object $disruption): string {
        $delays = [
            'weather' => ['LOW' => '2-4 hours', 'MEDIUM' => '4-8 hours', 'HIGH' => '8-16 hours', 'CRITICAL' => '16-36 hours'],
            'road_closure' => ['LOW' => '1-3 hours', 'MEDIUM' => '3-6 hours', 'HIGH' => '6-12 hours', 'CRITICAL' => '12-24 hours'],
            'port_congestion' => ['LOW' => '4-8 hours', 'MEDIUM' => '12-24 hours', 'HIGH' => '24-48 hours', 'CRITICAL' => '48-72 hours'],
            'vehicle_breakdown' => ['LOW' => '1-2 hours', 'MEDIUM' => '2-4 hours', 'HIGH' => '4-8 hours', 'CRITICAL' => '8-16 hours'],
            'supply_disruption' => ['LOW' => '2-4 hours', 'MEDIUM' => '6-12 hours', 'HIGH' => '12-24 hours', 'CRITICAL' => '24-48 hours'],
        ];

        return $delays[$disruption->type][$disruption->severity] ?? '4-12 hours';
    }

    /**
     * Generate actionable recommendations
     */
    private function generateActions(object $disruption, array $shipments): array {
        $actions = [];

        // Count cold-chain shipments at risk
        $coldChainAtRisk = array_filter($shipments, fn($s) => $s->cold_chain_required);

        if ($disruption->type === 'weather' || $disruption->type === 'road_closure') {
            $actions[] = [
                'priority' => 'HIGH',
                'action' => "Identify alternative routes bypassing {$disruption->location}",
                'icon' => 'route'
            ];
        }

        if (count($shipments) > 3) {
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Notify all affected carriers and coordinate response',
                'icon' => 'bell'
            ];
        }

        if (!empty($coldChainAtRisk)) {
            $actions[] = [
                'priority' => 'CRITICAL',
                'action' => 'Ensure cold-chain integrity for ' . count($coldChainAtRisk) . ' temperature-sensitive shipments',
                'icon' => 'temperature-low'
            ];
        }

        $actions[] = [
            'priority' => 'MEDIUM',
            'action' => 'Check fleet for idle vehicles near affected zone for potential redeployment',
            'icon' => 'truck-moving'
        ];

        $highPriority = array_filter($shipments, fn($s) => $s->priority === 'critical' || $s->priority === 'high');
        if (!empty($highPriority)) {
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Prioritize rerouting for ' . count($highPriority) . ' high/critical priority shipments',
                'icon' => 'exclamation-triangle'
            ];
        }

        return $actions;
    }

    private function formatCurrency(float $value): string {
        if ($value >= 10000000) return '₹' . number_format($value / 10000000, 1) . ' Cr';
        if ($value >= 100000) return '₹' . number_format($value / 100000, 1) . ' Lakh';
        return '₹' . number_format($value);
    }
}

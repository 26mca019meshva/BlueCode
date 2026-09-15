<?php
/**
 * Copilot Service — AI Decision Engine
 * 
 * This is the CENTRAL intelligence layer of SupplyGuard AI.
 * Uses deterministic rule-based reasoning over application data to provide
 * structured, actionable responses to natural language queries.
 * 
 * Includes an AI provider abstraction for future external API integration.
 */
class CopilotService {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Process a natural language query and return structured response
     */
    public function processQuery(string $question): array {
        $question = strtolower(trim($question));

        // Intent detection via keyword matching
        if ($this->matchesIntent($question, ['immediate attention', 'urgent', 'critical shipment', 'need attention', 'priority', 'what should.*do first', 'priorit'])) {
            return $this->handleImmediateAttention($question);
        }

        if ($this->matchesIntent($question, ['biggest.*risk', 'main risk', 'overall risk', 'current risk', 'risk summary', 'logistics risk'])) {
            return $this->handleOverallRisk();
        }

        if ($this->matchesIntent($question, ['why.*high risk', 'why.*critical', 'why is.*risk', 'explain.*risk'])) {
            return $this->handleWhyHighRisk($question);
        }

        if ($this->matchesIntent($question, ['affected.*disruption', 'disruption.*affect', 'impact.*disruption', 'mumbai disruption', 'vadodara', 'jaipur', 'which.*affected'])) {
            return $this->handleAffectedByDisruption($question);
        }

        if ($this->matchesIntent($question, ['alternative route', 'reroute', 'suggest.*route', 'different route', 'alternate route'])) {
            return $this->handleRouteRecommendation($question);
        }

        if ($this->matchesIntent($question, ['idle vehicle', 'redeploy', 'available vehicle', 'fleet.*available', 'idle fleet', 'unused vehicle'])) {
            return $this->handleIdleVehicles();
        }

        if ($this->matchesIntent($question, ['temperature', 'cold chain', 'excursion', 'cold-chain', 'freezer', 'refriger'])) {
            return $this->handleColdChainStatus();
        }

        if ($this->matchesIntent($question, ['disruption', 'active disruption', 'current disruption', 'what disruption'])) {
            return $this->handleActiveDisruptions();
        }

        if ($this->matchesIntent($question, ['recommend', 'suggestion', 'what should', 'action plan', 'next step'])) {
            return $this->handleImmediateAttention($question);
        }

        if ($this->matchesIntent($question, ['shipment', 'sh-', 'cc-'])) {
            return $this->handleShipmentQuery($question);
        }

        if ($this->matchesIntent($question, ['fleet', 'vehicle', 'truck'])) {
            return $this->handleFleetOverview();
        }

        // Default: provide a helpful overview
        return $this->handleGeneralQuery($question);
    }

    /**
     * Check if question matches any intent keywords
     */
    private function matchesIntent(string $question, array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '.*')) {
                if (preg_match('/' . $pattern . '/i', $question)) return true;
            } else {
                if (str_contains($question, $pattern)) return true;
            }
        }
        return false;
    }

    /**
     * Handle: "Which shipments need immediate attention?"
     */
    private function handleImmediateAttention(string $question): array {
        $this->db->query("SELECT s.*, f.vehicle_code FROM shipments s LEFT JOIN fleet_assets f ON s.vehicle_id = f.id WHERE s.risk_level IN ('HIGH', 'CRITICAL') AND s.status NOT IN ('delivered', 'cancelled') ORDER BY s.risk_score DESC LIMIT 10");
        $shipments = $this->db->resultSet();

        if (empty($shipments)) {
            return $this->formatResponse('✅ No shipments currently require immediate attention. All shipments are within acceptable risk levels.', []);
        }

        $items = [];
        $actions = [];
        foreach ($shipments as $i => $s) {
            $reason = $this->getQuickRiskReason($s);
            $action = $this->getQuickAction($s);
            $items[] = [
                'title' => "{$s->shipment_code}",
                'subtitle' => "{$s->origin} → {$s->destination} | {$s->cargo_type}",
                'risk_level' => $s->risk_level,
                'risk_score' => $s->risk_score,
                'reason' => $reason,
                'action' => $action,
                'shipment_id' => $s->id
            ];
            $actions[] = "**{$s->shipment_code}** ({$s->risk_level}, Score: {$s->risk_score}/100): {$action}";
        }

        $count = count($shipments);
        $summary = "🚨 **{$count} shipments require immediate attention.**\n\n";
        $summary .= "**Prioritized Actions (most urgent first):**\n\n";
        foreach ($actions as $idx => $a) {
            $summary .= ($idx + 1) . ". {$a}\n";
        }

        return $this->formatResponse($summary, $items);
    }

    /**
     * Handle: "What is the biggest current logistics risk?"
     */
    private function handleOverallRisk(): array {
        // Active disruptions
        $this->db->query("SELECT * FROM disruptions WHERE status = 'active' ORDER BY CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 WHEN 'LOW' THEN 4 ELSE 5 END LIMIT 5");
        $disruptions = $this->db->resultSet();

        // High risk shipments
        $this->db->query("SELECT COUNT(*) as c FROM shipments WHERE risk_level IN ('HIGH', 'CRITICAL') AND status NOT IN ('delivered', 'cancelled')");
        $highRiskCount = $this->db->single()->c;

        // Cargo value at risk
        $this->db->query("SELECT SUM(cargo_value) as total FROM shipments WHERE risk_level IN ('HIGH', 'CRITICAL') AND status NOT IN ('delivered', 'cancelled')");
        $cargoAtRisk = $this->db->single()->total ?? 0;

        // Cold chain alerts
        $this->db->query("SELECT COUNT(DISTINCT sr.shipment_id) as c FROM sensor_readings sr INNER JOIN (SELECT shipment_id, MAX(recorded_at) as max_r FROM sensor_readings GROUP BY shipment_id) latest ON sr.shipment_id = latest.shipment_id AND sr.recorded_at = latest.max_r JOIN shipments s ON sr.shipment_id = s.id WHERE s.cold_chain_required = 1 AND (sr.temperature > s.temp_max OR sr.temperature < s.temp_min)");
        $coldChainAlerts = $this->db->single()->c;

        // Idle fleet
        $this->db->query("SELECT COUNT(*) as c FROM fleet_assets WHERE status IN ('idle', 'available')");
        $availableFleet = $this->db->single()->c;

        $mainDisruption = !empty($disruptions) ? $disruptions[0] : null;
        $overallRisk = $highRiskCount > 5 ? 'CRITICAL' : ($highRiskCount > 2 ? 'HIGH' : 'MEDIUM');

        $summary = "## 📊 Overall Risk Assessment: **{$overallRisk}**\n\n";

        if ($mainDisruption) {
            $summary .= "### Main Risk\n";
            $summary .= "**{$mainDisruption->title}** ({$mainDisruption->severity})\n";
            $summary .= "📍 Location: {$mainDisruption->location}\n\n";
        }

        $summary .= "### Impact Summary\n";
        $summary .= "- 🚚 High-risk shipments: **{$highRiskCount}**\n";
        $summary .= "- 💰 Cargo value at risk: **₹" . number_format($cargoAtRisk / 100000, 1) . " Lakh**\n";
        $summary .= "- 🌡️ Cold-chain alerts: **{$coldChainAlerts}**\n";
        $summary .= "- 🚛 Available fleet: **{$availableFleet}** vehicles\n\n";

        $summary .= "### Priority Actions\n";
        $summary .= "1. Reroute high-priority shipments away from active disruption zones\n";
        $summary .= "2. Redeploy {$availableFleet} available vehicles to support affected shipments\n";
        if ($coldChainAlerts > 0) {
            $summary .= "3. Address {$coldChainAlerts} cold-chain temperature excursion(s) immediately\n";
        }

        return $this->formatResponse($summary, []);
    }

    /**
     * Handle: "Why is SH-1024 high risk?"
     */
    private function handleWhyHighRisk(string $question): array {
        // Extract shipment code
        preg_match('/(sh-\d+|cc-\d+)/i', $question, $matches);
        $code = strtoupper($matches[1] ?? '');

        if (empty($code)) {
            return $this->formatResponse("Please specify a shipment code (e.g., SH-1024 or CC-204).", []);
        }

        $this->db->query("SELECT s.*, f.vehicle_code FROM shipments s LEFT JOIN fleet_assets f ON s.vehicle_id = f.id WHERE s.shipment_code = :code");
        $this->db->bind(':code', $code);
        $shipment = $this->db->single();

        if (!$shipment) {
            return $this->formatResponse("Shipment **{$code}** not found in the system.", []);
        }

        // Run risk analysis
        require_once APP_ROOT . '/services/RiskAnalysisService.php';
        $riskService = new RiskAnalysisService();
        $analysis = $riskService->analyzeShipment($shipment);

        $summary = "## 📋 Risk Analysis: {$code}\n\n";
        $summary .= "| Field | Value |\n|---|---|\n";
        $summary .= "| Route | {$shipment->origin} → {$shipment->destination} |\n";
        $summary .= "| Current Location | {$shipment->current_location} |\n";
        $summary .= "| Cargo | {$shipment->cargo_type} |\n";
        $summary .= "| Cargo Value | ₹" . number_format($shipment->cargo_value) . " |\n";
        $summary .= "| Priority | " . strtoupper($shipment->priority) . " |\n";
        $summary .= "| Status | {$shipment->status} |\n";
        $summary .= "| **Risk Score** | **{$analysis['risk_score']}/100** |\n";
        $summary .= "| **Risk Level** | **{$analysis['risk_level']}** |\n\n";

        if (!empty($analysis['risk_reasons'])) {
            $summary .= "### Risk Factors\n";
            foreach ($analysis['risk_reasons'] as $reason) {
                $summary .= "- ⚠️ {$reason}\n";
            }
        }

        return $this->formatResponse($summary, [['shipment_id' => $shipment->id, 'title' => $code]]);
    }

    /**
     * Handle: "Which shipments are affected by the Vadodara disruption?"
     */
    private function handleAffectedByDisruption(string $question): array {
        // Try to find location in question
        $locations = ['mumbai', 'vadodara', 'surat', 'ahmedabad', 'pune', 'delhi', 'jaipur', 'bengaluru', 'chennai', 'hyderabad'];
        $targetLocation = '';
        foreach ($locations as $loc) {
            if (str_contains($question, $loc)) {
                $targetLocation = ucfirst($loc);
                break;
            }
        }

        if (empty($targetLocation)) {
            // Get the most severe active disruption
            $this->db->query("SELECT * FROM disruptions WHERE status = 'active' ORDER BY CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 WHEN 'LOW' THEN 4 ELSE 5 END LIMIT 1");
            $disruption = $this->db->single();
            $targetLocation = $disruption ? $disruption->location : 'Vadodara';
        }

        $this->db->query("SELECT * FROM disruptions WHERE status = 'active' AND location LIKE :loc LIMIT 1");
        $this->db->bind(':loc', '%' . $targetLocation . '%');
        $disruption = $this->db->single();

        $this->db->query("SELECT s.* FROM shipments s WHERE s.status NOT IN ('delivered', 'cancelled') AND (s.current_location LIKE :loc1 OR s.route_via LIKE :loc2) ORDER BY s.risk_score DESC");
        $this->db->bind(':loc1', '%' . $targetLocation . '%');
        $this->db->bind(':loc2', '%' . $targetLocation . '%');
        $affected = $this->db->resultSet();

        $totalValue = array_sum(array_map(fn($s) => (float)$s->cargo_value, $affected));

        $summary = "## 🔍 Disruption Impact: {$targetLocation}\n\n";
        if ($disruption) {
            $summary .= "**{$disruption->title}** (Severity: {$disruption->severity})\n\n";
        }
        $summary .= "**{count($affected)} shipments affected** | Cargo Value: ₹" . number_format($totalValue / 100000, 1) . " Lakh\n\n";

        if (!empty($affected)) {
            $summary .= "| # | Code | Route | Cargo | Risk | Priority |\n";
            $summary .= "|---|------|-------|-------|------|----------|\n";
            foreach (array_slice($affected, 0, 8) as $i => $s) {
                $summary .= "| " . ($i + 1) . " | {$s->shipment_code} | {$s->origin}→{$s->destination} | {$s->cargo_type} | {$s->risk_level} | {$s->priority} |\n";
            }
        }

        $count = count($affected);
        $summary = str_replace('{count($affected)}', $count, $summary);

        return $this->formatResponse($summary, array_map(fn($s) => ['shipment_id' => $s->id, 'title' => $s->shipment_code], $affected));
    }

    /**
     * Handle route recommendation queries
     */
    private function handleRouteRecommendation(string $question): array {
        preg_match('/(sh-\d+|cc-\d+)/i', $question, $matches);
        $code = strtoupper($matches[1] ?? 'SH-1024');

        $this->db->query("SELECT * FROM shipments WHERE shipment_code = :code");
        $this->db->bind(':code', $code);
        $shipment = $this->db->single();

        if (!$shipment) {
            return $this->formatResponse("Shipment **{$code}** not found.", []);
        }

        require_once APP_ROOT . '/services/RouteRecommendationService.php';
        $routeService = new RouteRecommendationService();
        $recommendation = $routeService->getAlternativeRoute($shipment);

        if (!$recommendation) {
            return $this->formatResponse("No alternative routes found for **{$code}** ({$shipment->origin} → {$shipment->destination}).", []);
        }

        $current = $recommendation['current_route'];
        $alt = $recommendation['recommended_route'];

        $summary = "## 🗺️ Route Recommendation: {$code}\n\n";
        $summary .= "### ❌ Current Route (Affected)\n";
        $summary .= "**{$current['origin']}** → " . implode(' → ', $current['via']) . " → **{$current['destination']}**\n";
        $summary .= "Distance: {$current['distance_km']} km | Status: {$current['status']} | Risk: {$current['risk_level']}\n\n";

        $summary .= "### ✅ Recommended Route\n";
        $summary .= "**{$alt['origin']}** → " . implode(' → ', $alt['via']) . " → **{$alt['destination']}**\n";
        $summary .= "Distance: {$alt['distance_km']} km | Status: {$alt['status']} | Risk: {$alt['risk_level']}\n\n";

        $summary .= "### 💡 Recommendation\n";
        $summary .= $recommendation['reason'] . "\n\n";
        $summary .= "*Note: Route data is simulated for prototype demonstration.*";

        return $this->formatResponse($summary, [['shipment_id' => $shipment->id, 'title' => $code]]);
    }

    /**
     * Handle idle vehicle queries
     */
    private function handleIdleVehicles(): array {
        $this->db->query("SELECT * FROM fleet_assets WHERE status IN ('idle', 'available') ORDER BY vehicle_code");
        $vehicles = $this->db->resultSet();

        if (empty($vehicles)) {
            return $this->formatResponse("No idle or available vehicles found. All fleet assets are currently in use or under maintenance.", []);
        }

        $summary = "## 🚛 Available Fleet for Redeployment\n\n";
        $summary .= "**" . count($vehicles) . " vehicles available**\n\n";
        $summary .= "| Vehicle | Type | Location | Capacity | Temp Capable | Fuel | Status |\n";
        $summary .= "|---------|------|----------|----------|--------------|------|--------|\n";

        foreach ($vehicles as $v) {
            $tempCapable = $v->temperature_capable ? '✅ Yes' : '❌ No';
            $summary .= "| {$v->vehicle_code} | {$v->vehicle_type} | {$v->current_location} | {$v->capacity_tons}T | {$tempCapable} | {$v->fuel_level}% | {$v->status} |\n";
        }

        // Add redeployment suggestion
        require_once APP_ROOT . '/services/FleetOptimizationService.php';
        $fleetService = new FleetOptimizationService();
        $redeployments = $fleetService->findRedeploymentOptions();

        if (!empty($redeployments)) {
            $summary .= "\n### 🎯 Top Redeployment Recommendations\n\n";
            foreach (array_slice($redeployments, 0, 3) as $r) {
                $summary .= "- **{$r['vehicle']['code']}** → **{$r['shipment']['code']}** ({$r['estimated_distance_km']} km away, Score: {$r['suitability_score']}/100)\n";
            }
        }

        return $this->formatResponse($summary, []);
    }

    /**
     * Handle cold chain queries
     */
    private function handleColdChainStatus(): array {
        require_once APP_ROOT . '/services/ColdChainService.php';
        $coldChainService = new ColdChainService();
        $alerts = $coldChainService->getAlerts();

        $this->db->query("SELECT COUNT(*) as c FROM shipments WHERE cold_chain_required = 1 AND status NOT IN ('delivered', 'cancelled')");
        $totalColdChain = $this->db->single()->c;

        $summary = "## 🌡️ Cold Chain Status\n\n";
        $summary .= "**{$totalColdChain} active cold-chain shipments** | **" . count($alerts) . " alerts**\n\n";

        if (empty($alerts)) {
            $summary .= "✅ All cold-chain shipments are within acceptable temperature ranges.\n";
        } else {
            $summary .= "### ⚠️ Temperature Excursions\n\n";
            foreach ($alerts as $a) {
                $icon = match($a['severity']) {
                    'CRITICAL' => '🔴',
                    'HIGH' => '🟠',
                    'WARNING' => '🟡',
                    default => '🟢'
                };
                $summary .= "{$icon} **{$a['shipment_code']}** ({$a['cargo_type']})\n";
                $summary .= "   Current: {$a['current_temp']}°C | Range: {$a['temp_min']}°C – {$a['temp_max']}°C | Severity: **{$a['severity']}**\n";
                $summary .= "   → {$a['recommendation']}\n\n";
            }
        }

        return $this->formatResponse($summary, array_map(fn($a) => ['shipment_id' => $a['shipment_id'], 'title' => $a['shipment_code']], $alerts));
    }

    /**
     * Handle active disruptions query
     */
    private function handleActiveDisruptions(): array {
        $this->db->query("SELECT * FROM disruptions WHERE status = 'active' ORDER BY CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 WHEN 'LOW' THEN 4 ELSE 5 END");
        $disruptions = $this->db->resultSet();

        $summary = "## ⚡ Active Disruptions (" . count($disruptions) . ")\n\n";

        foreach ($disruptions as $d) {
            $icon = match($d->severity) { 'CRITICAL' => '🔴', 'HIGH' => '🟠', 'MEDIUM' => '🟡', default => '🟢' };
            $summary .= "{$icon} **{$d->title}**\n";
            $summary .= "   Type: {$d->type} | Location: {$d->location} | Severity: {$d->severity}\n";
            $summary .= "   Started: " . date('M d, H:i', strtotime($d->start_time)) . "\n";
            if ($d->estimated_end_time) {
                $summary .= "   Estimated End: " . date('M d, H:i', strtotime($d->estimated_end_time)) . "\n";
            }
            $summary .= "\n";
        }

        return $this->formatResponse($summary, []);
    }

    /**
     * Handle specific shipment queries
     */
    private function handleShipmentQuery(string $question): array {
        preg_match('/(sh-\d+|cc-\d+)/i', $question, $matches);
        if (!empty($matches[1])) {
            return $this->handleWhyHighRisk($question);
        }

        // General shipment overview
        $this->db->query("SELECT COUNT(*) as total, SUM(CASE WHEN risk_level = 'HIGH' THEN 1 ELSE 0 END) as high, SUM(CASE WHEN risk_level = 'CRITICAL' THEN 1 ELSE 0 END) as critical, SUM(CASE WHEN status = 'delayed' THEN 1 ELSE 0 END) as delayed FROM shipments WHERE status NOT IN ('delivered', 'cancelled')");
        $stats = $this->db->single();

        $summary = "## 📦 Shipment Overview\n\n";
        $summary .= "- Total active: **{$stats->total}**\n";
        $summary .= "- Critical risk: **{$stats->critical}**\n";
        $summary .= "- High risk: **{$stats->high}**\n";
        $summary .= "- Delayed: **{$stats->delayed}**\n";

        return $this->formatResponse($summary, []);
    }

    /**
     * Handle fleet overview
     */
    private function handleFleetOverview(): array {
        $this->db->query("SELECT status, COUNT(*) as c FROM fleet_assets GROUP BY status");
        $stats = $this->db->resultSet();

        $summary = "## 🚛 Fleet Overview\n\n";
        foreach ($stats as $s) {
            $summary .= "- " . ucfirst(str_replace('_', ' ', $s->status)) . ": **{$s->c}**\n";
        }

        return $this->formatResponse($summary, []);
    }

    /**
     * Handle general/unrecognized queries
     */
    private function handleGeneralQuery(string $question): array {
        $summary = "I can help you with the following:\n\n";
        $summary .= "- 📦 **Shipment queries**: \"Which shipments need immediate attention?\"\n";
        $summary .= "- ⚠️ **Risk analysis**: \"Why is SH-1024 high risk?\"\n";
        $summary .= "- 🌧️ **Disruption impact**: \"Which shipments are affected by the Vadodara disruption?\"\n";
        $summary .= "- 🗺️ **Route suggestions**: \"Suggest an alternative route for SH-1024\"\n";
        $summary .= "- 🚛 **Fleet management**: \"Which idle vehicles can be redeployed?\"\n";
        $summary .= "- 🌡️ **Cold chain**: \"Which cold-chain shipments have temperature excursions?\"\n";
        $summary .= "- 📊 **Risk overview**: \"What is the biggest current logistics risk?\"\n\n";
        $summary .= "Try asking one of these questions!";

        return $this->formatResponse($summary, []);
    }

    /**
     * Get quick risk reason for a shipment
     */
    private function getQuickRiskReason(object $s): string {
        if ($s->cold_chain_required) return 'Cold-chain temperature concern';
        if ($s->status === 'delayed') return 'Shipment delayed on route';
        if ($s->status === 'at_risk') return 'Shipment flagged as at-risk';
        if ($s->priority === 'critical') return 'Critical priority shipment';
        return 'Elevated risk score';
    }

    /**
     * Get quick action for a shipment
     */
    private function getQuickAction(object $s): string {
        if ($s->cold_chain_required && $s->status === 'at_risk') return 'Transfer to refrigerated vehicle immediately';
        if ($s->status === 'delayed') return 'Consider rerouting or assigning alternative vehicle';
        if ($s->status === 'at_risk') return 'Reroute away from disruption zone';
        return 'Monitor closely and prepare contingency';
    }

    /**
     * Format copilot response
     */
    private function formatResponse(string $message, array $relatedItems = []): array {
        return [
            'success' => true,
            'message' => $message,
            'related_items' => $relatedItems,
            'timestamp' => date('Y-m-d H:i:s'),
            'provider' => 'local_engine',
            'disclaimer' => 'Analysis based on current database records. Route recommendations use simulated data.'
        ];
    }
}

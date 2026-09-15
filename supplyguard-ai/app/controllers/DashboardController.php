<?php
/**
 * Dashboard Controller
 */
class DashboardController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $shipmentModel = $this->model('Shipment');
        $disruptionModel = $this->model('Disruption');
        $fleetModel = $this->model('Fleet');
        $sensorModel = $this->model('SensorReading');
        $recModel = $this->model('Recommendation');
        $logModel = $this->model('ActivityLog');

        // KPI Data
        $totalShipments = $shipmentModel->getCount();
        $highRiskShipments = $shipmentModel->getCount(['risk_level' => 'HIGH']) + $shipmentModel->getCount(['risk_level' => 'CRITICAL']);
        $activeDisruptions = $disruptionModel->getActiveCount();
        $coldChainAlerts = $sensorModel->getColdChainAlertCount();
        $fleetCounts = $fleetModel->getStatusCounts();

        // Charts Data
        $riskDistribution = $shipmentModel->getRiskDistribution();
        $statusDistribution = $shipmentModel->getStatusDistribution();

        // Top at-risk shipments
        $topRiskShipments = $shipmentModel->getTopAtRisk(5);

        // Active disruptions list
        $disruptions = $disruptionModel->getActive();

        // Recent recommendations
        $recentRecs = $recModel->getRecent(5);

        // Activity log
        $recentActivity = $logModel->getRecent(8);

        // Fleet utilization
        $avgUtilization = $fleetModel->getAvgUtilization();

        // Cargo value at risk
        $cargoValueAtRisk = $shipmentModel->getTotalCargoValueAtRisk();

        $data = [
            'pageTitle' => 'Dashboard',
            'totalShipments' => $totalShipments,
            'highRiskShipments' => $highRiskShipments,
            'activeDisruptions' => $activeDisruptions,
            'coldChainAlerts' => $coldChainAlerts,
            'fleetCounts' => $fleetCounts,
            'riskDistribution' => $riskDistribution,
            'statusDistribution' => $statusDistribution,
            'topRiskShipments' => $topRiskShipments,
            'disruptions' => $disruptions,
            'recentRecs' => $recentRecs,
            'recentActivity' => $recentActivity,
            'avgUtilization' => $avgUtilization,
            'cargoValueAtRisk' => $cargoValueAtRisk,
            'pageScripts' => ['dashboard.js', 'charts.js']
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('dashboard/index', $data);
        $this->view('layouts/footer', $data);
    }
}

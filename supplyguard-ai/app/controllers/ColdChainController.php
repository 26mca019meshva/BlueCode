<?php
/**
 * Cold Chain Controller
 */
class ColdChainController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $coldChainService = $this->service('ColdChainService');
        $monitoringData = $coldChainService->getMonitoringDashboard();

        $data = [
            'pageTitle' => 'Cold Chain Monitor',
            'shipments' => $monitoringData['shipments'],
            'alertCount' => $monitoringData['alertCount'],
            'normalCount' => $monitoringData['normalCount'],
            'pageScripts' => ['charts.js']
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('cold-chain/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function show(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->redirect('cold-chain');

        $shipmentModel = $this->model('Shipment');
        $sensorModel = $this->model('SensorReading');
        $shipment = $shipmentModel->getById($id);

        if (!$shipment || !$shipment->cold_chain_required) {
            Session::setFlash('error', 'Cold-chain shipment not found.');
            $this->redirect('cold-chain');
        }

        $readings = $sensorModel->getByShipment($id);
        $latest = $sensorModel->getLatest($id);
        
        $coldChainService = $this->service('ColdChainService');
        $severity = $coldChainService->classifySeverity($latest, $shipment);
        $recommendation = $coldChainService->getRecommendation($severity, $shipment);

        $recModel = $this->model('Recommendation');
        $recommendations = $recModel->getByShipment($id);

        $data = [
            'pageTitle' => $shipment->shipment_code . ' — Cold Chain',
            'shipment' => $shipment,
            'readings' => $readings,
            'latest' => $latest,
            'severity' => $severity,
            'recommendation' => $recommendation,
            'recommendations' => $recommendations,
            'pageScripts' => ['charts.js']
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('cold-chain/show', $data);
        $this->view('layouts/footer', $data);
    }

    public function getTemperatureData(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->json(['error' => 'Missing ID'], 400);

        $sensorModel = $this->model('SensorReading');
        $readings = $sensorModel->getByShipment($id);

        $chartData = array_map(function($r) {
            return [
                'time' => date('H:i', strtotime($r->recorded_at)),
                'temp' => (float)$r->temperature,
                'humidity' => (float)($r->humidity ?? 0)
            ];
        }, $readings);

        $this->json($chartData);
    }
}

<?php
/**
 * Report Controller
 */
class ReportController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $data = ['pageTitle' => 'Reports'];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('reports/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function shipmentRisk(): void {
        $this->requireAuth();
        $model = $this->model('Shipment');
        $shipments = $model->getAll([], 100);
        
        if ($this->getQuery('export') === 'csv') {
            $this->exportCsv('shipment_risk_report', ['Code','Origin','Destination','Cargo','Value','Priority','Risk Score','Risk Level','Status'], 
                array_map(fn($s) => [$s->shipment_code, $s->origin, $s->destination, $s->cargo_type, $s->cargo_value, $s->priority, $s->risk_score, $s->risk_level, $s->status], $shipments));
        }

        $data = ['pageTitle' => 'Shipment Risk Report', 'shipments' => $shipments, 'pageScripts' => ['charts.js']];
        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('reports/shipment-risk', $data);
        $this->view('layouts/footer', $data);
    }

    public function disruptionImpact(): void {
        $this->requireAuth();
        $model = $this->model('Disruption');
        $shipmentModel = $this->model('Shipment');
        $disruptions = $model->getAll();

        $impactData = [];
        foreach ($disruptions as $d) {
            $affected = $shipmentModel->getAffectedByDisruption($d->location);
            $impactData[] = (object)[
                'disruption' => $d,
                'affected_count' => count($affected),
                'cargo_value' => array_sum(array_map(fn($s) => $s->cargo_value, $affected))
            ];
        }

        $data = ['pageTitle' => 'Disruption Impact Report', 'impactData' => $impactData, 'pageScripts' => ['charts.js']];
        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('reports/disruption-impact', $data);
        $this->view('layouts/footer', $data);
    }

    public function fleetUtilization(): void {
        $this->requireAuth();
        $model = $this->model('Fleet');
        $fleet = $model->getAll();
        $counts = $model->getStatusCounts();

        $data = ['pageTitle' => 'Fleet Utilization Report', 'fleet' => $fleet, 'counts' => $counts, 'pageScripts' => ['charts.js']];
        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('reports/fleet-utilization', $data);
        $this->view('layouts/footer', $data);
    }

    public function coldChain(): void {
        $this->requireAuth();
        $coldChainService = $this->service('ColdChainService');
        $monitoringData = $coldChainService->getMonitoringDashboard();

        $data = ['pageTitle' => 'Cold Chain Alert Report', 'shipments' => $monitoringData['shipments']];
        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('reports/cold-chain', $data);
        $this->view('layouts/footer', $data);
    }

    public function activity(): void {
        $this->requireRole(ROLE_ADMIN);
        $logModel = $this->model('ActivityLog');
        $logs = $logModel->getAll(100);

        $data = ['pageTitle' => 'Activity Log', 'logs' => $logs];
        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('reports/activity', $data);
        $this->view('layouts/footer', $data);
    }

    private function exportCsv(string $filename, array $headers, array $rows): void {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}

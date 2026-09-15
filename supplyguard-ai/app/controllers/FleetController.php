<?php
/**
 * Fleet Controller
 */
class FleetController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $model = $this->model('Fleet');
        $statusFilter = $this->getQuery('status', '');
        $fleet = $model->getAll($statusFilter);
        $counts = $model->getStatusCounts();

        $data = [
            'pageTitle' => 'Fleet Management',
            'fleet' => $fleet,
            'counts' => $counts,
            'statusFilter' => $statusFilter
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('fleet/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function show(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->redirect('fleet');

        $model = $this->model('Fleet');
        $vehicle = $model->getById($id);
        if (!$vehicle) {
            Session::setFlash('error', 'Vehicle not found.');
            $this->redirect('fleet');
        }

        $data = [
            'pageTitle' => $vehicle->vehicle_code,
            'vehicle' => $vehicle
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('fleet/show', $data);
        $this->view('layouts/footer', $data);
    }

    public function findRedeployment(): void {
        $this->requireAuth();

        $fleetService = $this->service('FleetOptimizationService');
        $redeployments = $fleetService->findRedeploymentOptions();

        if ($this->isAjax()) {
            $this->json($redeployments);
        }

        $data = [
            'pageTitle' => 'Fleet Redeployment',
            'redeployments' => $redeployments
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('fleet/redeployment', $data);
        $this->view('layouts/footer', $data);
    }
}

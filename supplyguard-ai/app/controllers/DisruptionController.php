<?php
/**
 * Disruption Controller
 */
class DisruptionController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $model = $this->model('Disruption');
        $statusFilter = $this->getQuery('status', '');
        $disruptions = $model->getAll($statusFilter);

        $data = [
            'pageTitle' => 'Disruption Center',
            'disruptions' => $disruptions,
            'statusFilter' => $statusFilter
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('disruptions/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function show(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->redirect('disruptions');

        $model = $this->model('Disruption');
        $disruption = $model->getById($id);
        if (!$disruption) {
            Session::setFlash('error', 'Disruption not found.');
            $this->redirect('disruptions');
        }

        // Get affected shipments
        $shipmentModel = $this->model('Shipment');
        $affectedShipments = $shipmentModel->getAffectedByDisruption($disruption->location);

        // Get recommendations for this disruption
        $recModel = $this->model('Recommendation');
        $recommendations = $recModel->getByDisruption($id);

        // Calculate impact
        $disruptionService = $this->service('DisruptionAnalysisService');
        $impact = $disruptionService->analyzeImpact($disruption, $affectedShipments);

        $this->logActivity('view', 'disruptions', "Viewed disruption: {$disruption->title}");

        $data = [
            'pageTitle' => $disruption->title,
            'disruption' => $disruption,
            'affectedShipments' => $affectedShipments,
            'recommendations' => $recommendations,
            'impact' => $impact,
            'pageScripts' => ['charts.js']
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('disruptions/show', $data);
        $this->view('layouts/footer', $data);
    }

    public function create(): void {
        $this->requireRole(ROLE_ADMIN);
        $data = ['pageTitle' => 'Create Disruption'];
        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('disruptions/create', $data);
        $this->view('layouts/footer', $data);
    }

    public function store(): void {
        $this->requireRole(ROLE_ADMIN);
        if (!$this->isPost() || !$this->verifyCsrf()) {
            $this->redirect('disruptions/create');
        }

        $model = $this->model('Disruption');
        $disruptionData = [
            'title' => $this->getPost('title'),
            'type' => $this->getPost('type'),
            'location' => $this->getPost('location'),
            'severity' => $this->getPost('severity'),
            'description' => $this->getPost('description'),
            'affected_routes' => $this->getPost('affected_routes'),
            'radius_km' => $this->getPost('radius_km', 50),
            'start_time' => $this->getPost('start_time'),
            'estimated_end_time' => $this->getPost('estimated_end_time') ?: null,
            'status' => $this->getPost('status', 'active')
        ];

        $id = $model->create($disruptionData);
        if ($id) {
            $this->logActivity('create', 'disruptions', "Created disruption: {$disruptionData['title']}");
            Session::setFlash('success', 'Disruption created.');
            $this->redirect('disruptions/show/' . $id);
        } else {
            Session::setFlash('error', 'Failed to create disruption.');
            $this->redirect('disruptions/create');
        }
    }

    public function analyzeImpact(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->json(['error' => 'Missing ID'], 400);

        $model = $this->model('Disruption');
        $disruption = $model->getById($id);
        if (!$disruption) $this->json(['error' => 'Not found'], 404);

        $shipmentModel = $this->model('Shipment');
        $affected = $shipmentModel->getAffectedByDisruption($disruption->location);

        $disruptionService = $this->service('DisruptionAnalysisService');
        $impact = $disruptionService->analyzeImpact($disruption, $affected);

        $this->logActivity('analyze', 'disruptions', "Analyzed impact: {$disruption->title}");
        $this->json($impact);
    }
}

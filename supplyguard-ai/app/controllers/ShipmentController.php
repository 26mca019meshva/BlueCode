<?php
/**
 * Shipment Controller
 */
class ShipmentController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $model = $this->model('Shipment');

        $filters = [
            'status' => $this->getQuery('status', ''),
            'risk_level' => $this->getQuery('risk_level', ''),
            'cargo_type' => $this->getQuery('cargo_type', ''),
            'priority' => $this->getQuery('priority', ''),
            'search' => $this->getQuery('search', '')
        ];
        $filters = array_filter($filters);

        $page = max(1, (int)$this->getQuery('page', 1));
        $total = $model->getCount($filters);
        $shipments = $model->getAll($filters, ITEMS_PER_PAGE, ($page - 1) * ITEMS_PER_PAGE);

        $data = [
            'pageTitle' => 'Shipments',
            'shipments' => $shipments,
            'filters' => $filters,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / ITEMS_PER_PAGE)
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('shipments/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function show(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->redirect('shipments');

        $model = $this->model('Shipment');
        $shipment = $model->getById($id);
        if (!$shipment) {
            Session::setFlash('error', 'Shipment not found.');
            $this->redirect('shipments');
        }

        // Get recommendations and sensor data
        $recModel = $this->model('Recommendation');
        $sensorModel = $this->model('SensorReading');
        
        $recommendations = $recModel->getByShipment($id);
        $sensorReadings = $shipment->cold_chain_required ? $sensorModel->getByShipment($id) : [];
        $latestReading = $shipment->cold_chain_required ? $sensorModel->getLatest($id) : null;

        // Risk analysis
        $riskService = $this->service('RiskAnalysisService');
        $riskAnalysis = $riskService->analyzeShipment($shipment);

        $this->logActivity('view', 'shipments', "Viewed shipment: {$shipment->shipment_code}");

        $data = [
            'pageTitle' => $shipment->shipment_code,
            'shipment' => $shipment,
            'recommendations' => $recommendations,
            'sensorReadings' => $sensorReadings,
            'latestReading' => $latestReading,
            'riskAnalysis' => $riskAnalysis,
            'pageScripts' => ['charts.js']
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('shipments/show', $data);
        $this->view('layouts/footer', $data);
    }

    public function create(): void {
        $this->requireRole(ROLE_ADMIN);

        $fleetModel = $this->model('Fleet');
        $data = [
            'pageTitle' => 'Add Shipment',
            'fleetAssets' => $fleetModel->getAll()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('shipments/create', $data);
        $this->view('layouts/footer', $data);
    }

    public function store(): void {
        $this->requireRole(ROLE_ADMIN);
        if (!$this->isPost() || !$this->verifyCsrf()) {
            $this->redirect('shipments/create');
        }

        $validator = new Validator($_POST);
        $validator->required('shipment_code', 'Shipment Code')
                  ->required('origin', 'Origin')
                  ->required('destination', 'Destination')
                  ->required('cargo_type', 'Cargo Type')
                  ->required('cargo_value', 'Cargo Value')
                  ->numeric('cargo_value', 'Cargo Value');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('shipments/create');
        }

        $model = $this->model('Shipment');
        $data = [
            'shipment_code' => $this->getPost('shipment_code'),
            'origin' => $this->getPost('origin'),
            'destination' => $this->getPost('destination'),
            'current_location' => $this->getPost('current_location') ?: $this->getPost('origin'),
            'cargo_type' => $this->getPost('cargo_type'),
            'cargo_description' => $this->getPost('cargo_description'),
            'cargo_value' => $this->getPost('cargo_value'),
            'weight_kg' => $this->getPost('weight_kg', 0),
            'priority' => $this->getPost('priority', 'medium'),
            'carrier' => $this->getPost('carrier'),
            'vehicle_id' => $this->getPost('vehicle_id') ?: null,
            'status' => $this->getPost('status', 'pending'),
            'eta' => $this->getPost('eta') ?: null,
            'delivery_deadline' => $this->getPost('delivery_deadline') ?: null,
            'cold_chain_required' => $this->getPost('cold_chain_required') ? 1 : 0,
            'temp_min' => $this->getPost('temp_min') ?: null,
            'temp_max' => $this->getPost('temp_max') ?: null,
            'route_via' => $this->getPost('route_via')
        ];

        $id = $model->create($data);
        if ($id) {
            $this->logActivity('create', 'shipments', "Created shipment: {$data['shipment_code']}");
            Session::setFlash('success', 'Shipment created successfully.');
            $this->redirect('shipments/show/' . $id);
        } else {
            Session::setFlash('error', 'Failed to create shipment.');
            $this->redirect('shipments/create');
        }
    }

    public function edit(int $id = 0): void {
        $this->requireRole(ROLE_ADMIN);
        if (!$id) $this->redirect('shipments');

        $model = $this->model('Shipment');
        $shipment = $model->getById($id);
        if (!$shipment) {
            Session::setFlash('error', 'Shipment not found.');
            $this->redirect('shipments');
        }

        $fleetModel = $this->model('Fleet');
        $data = [
            'pageTitle' => 'Edit ' . $shipment->shipment_code,
            'shipment' => $shipment,
            'fleetAssets' => $fleetModel->getAll()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('shipments/edit', $data);
        $this->view('layouts/footer', $data);
    }

    public function update(int $id = 0): void {
        $this->requireRole(ROLE_ADMIN);
        if (!$id || !$this->isPost() || !$this->verifyCsrf()) {
            $this->redirect('shipments');
        }

        $model = $this->model('Shipment');
        $updateData = [
            'origin' => $this->getPost('origin'),
            'destination' => $this->getPost('destination'),
            'current_location' => $this->getPost('current_location'),
            'cargo_type' => $this->getPost('cargo_type'),
            'cargo_description' => $this->getPost('cargo_description'),
            'cargo_value' => $this->getPost('cargo_value'),
            'weight_kg' => $this->getPost('weight_kg'),
            'priority' => $this->getPost('priority'),
            'carrier' => $this->getPost('carrier'),
            'vehicle_id' => $this->getPost('vehicle_id') ?: null,
            'status' => $this->getPost('status'),
            'eta' => $this->getPost('eta') ?: null,
            'delivery_deadline' => $this->getPost('delivery_deadline') ?: null,
            'cold_chain_required' => $this->getPost('cold_chain_required') ? 1 : 0,
            'temp_min' => $this->getPost('temp_min') ?: null,
            'temp_max' => $this->getPost('temp_max') ?: null,
            'route_via' => $this->getPost('route_via')
        ];

        if ($model->update($id, $updateData)) {
            $this->logActivity('update', 'shipments', "Updated shipment ID: {$id}");
            Session::setFlash('success', 'Shipment updated successfully.');
        } else {
            Session::setFlash('error', 'Failed to update shipment.');
        }
        $this->redirect('shipments/show/' . $id);
    }

    public function delete(int $id = 0): void {
        $this->requireRole(ROLE_ADMIN);
        if (!$id) $this->redirect('shipments');

        $model = $this->model('Shipment');
        if ($model->delete($id)) {
            $this->logActivity('delete', 'shipments', "Deleted shipment ID: {$id}");
            Session::setFlash('success', 'Shipment deleted.');
        } else {
            Session::setFlash('error', 'Failed to delete shipment.');
        }
        $this->redirect('shipments');
    }

    public function riskAnalysis(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->json(['error' => 'Missing ID'], 400);

        $model = $this->model('Shipment');
        $shipment = $model->getById($id);
        if (!$shipment) $this->json(['error' => 'Not found'], 404);

        $riskService = $this->service('RiskAnalysisService');
        $analysis = $riskService->analyzeShipment($shipment);

        $this->json($analysis);
    }
}

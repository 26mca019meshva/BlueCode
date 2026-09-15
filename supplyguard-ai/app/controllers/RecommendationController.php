<?php
/**
 * Recommendation Controller
 */
class RecommendationController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $model = $this->model('Recommendation');
        $recommendations = $model->getAll();

        $data = [
            'pageTitle' => 'AI Recommendations',
            'recommendations' => $recommendations
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('recommendations/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function apply(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->redirect('recommendations');

        $model = $this->model('Recommendation');
        $model->updateStatus($id, 'accepted');
        $this->logActivity('update', 'recommendations', "Accepted recommendation ID: {$id}");
        Session::setFlash('success', 'Recommendation accepted.');
        $this->redirect('recommendations');
    }

    public function reject(int $id = 0): void {
        $this->requireAuth();
        if (!$id) $this->redirect('recommendations');

        $model = $this->model('Recommendation');
        $model->updateStatus($id, 'rejected');
        $this->logActivity('update', 'recommendations', "Rejected recommendation ID: {$id}");
        Session::setFlash('warning', 'Recommendation rejected.');
        $this->redirect('recommendations');
    }
}

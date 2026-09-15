<?php
/**
 * Copilot Controller
 */
class CopilotController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $data = [
            'pageTitle' => 'AI Copilot',
            'pageScripts' => ['copilot.js']
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/navbar', $data);
        $this->view('copilot/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function query(): void {
        $this->requireAuth();

        // Accept both JSON body and form data
        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true);
        $question = $jsonData['question'] ?? $this->getPost('question', '');

        if (empty($question)) {
            $this->json(['error' => 'Please enter a question.'], 400);
        }

        $copilotService = $this->service('CopilotService');
        $response = $copilotService->processQuery($question);

        $this->logActivity('query', 'copilot', "Asked: " . substr($question, 0, 100));
        $this->json($response);
    }
}

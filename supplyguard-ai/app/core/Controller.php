<?php
/**
 * SupplyGuard AI - Base Controller
 * 
 * Provides model loading and view rendering capabilities.
 */

class Controller {
    
    /**
     * Load a model class
     */
    protected function model(string $model): object {
        $modelFile = APP_ROOT . '/models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }
        throw new Exception("Model {$model} not found");
    }

    /**
     * Load a service class
     */
    protected function service(string $service): object {
        $serviceFile = APP_ROOT . '/services/' . $service . '.php';
        if (file_exists($serviceFile)) {
            require_once $serviceFile;
            return new $service();
        }
        throw new Exception("Service {$service} not found");
    }

    /**
     * Render a view with data
     */
    protected function view(string $view, array $data = []): void {
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            // Extract data to variables
            extract($data);
            require_once $viewFile;
        } else {
            throw new Exception("View {$view} not found");
        }
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void {
        header('Location: ' . URL_ROOT . ltrim($url, '/'));
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Get POST data safely
     */
    protected function getPost(string $key, mixed $default = null): mixed {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * Get GET data safely
     */
    protected function getQuery(string $key, mixed $default = null): mixed {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void {
        if (!Auth::isLoggedIn()) {
            Session::setFlash('error', 'Please login to access this page.');
            $this->redirect('login');
        }
    }

    /**
     * Require specific role
     */
    protected function requireRole(string|array $roles): void {
        $this->requireAuth();
        if (!Auth::hasRole($roles)) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrf(): bool {
        $token = $this->getPost('csrf_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!Auth::verifyCsrfToken($token)) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            Session::setFlash('error', 'Security token expired. Please try again.');
            return false;
        }
        return true;
    }

    /**
     * Log an activity
     */
    protected function logActivity(string $action, string $module, string $description = ''): void {
        try {
            $logModel = $this->model('ActivityLog');
            $logModel->log(Auth::getUserId(), $action, $module, $description);
        } catch (Exception $e) {
            error_log('Activity log error: ' . $e->getMessage());
        }
    }
}

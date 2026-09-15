<?php
/**
 * SupplyGuard AI - Router
 * 
 * Handles URL parsing and request routing to controllers.
 * Supports clean URLs via Apache mod_rewrite.
 */

class Router {
    private $currentController = 'DashboardController';
    private string $currentMethod = 'index';
    private array $params = [];

    /**
     * Route map for clean URL aliases
     */
    private array $routes = [
        '' => ['DashboardController', 'index'],
        'dashboard' => ['DashboardController', 'index'],
        'login' => ['AuthController', 'login'],
        'logout' => ['AuthController', 'logout'],
        'shipments' => ['ShipmentController', 'index'],
        'disruptions' => ['DisruptionController', 'index'],
        'fleet' => ['FleetController', 'index'],
        'cold-chain' => ['ColdChainController', 'index'],
        'copilot' => ['CopilotController', 'index'],
        'recommendations' => ['RecommendationController', 'index'],
        'reports' => ['ReportController', 'index'],
    ];

    public function __construct() {
        $url = $this->parseUrl();
        
        // Check for direct route match first
        $urlPath = implode('/', $url);
        if (isset($this->routes[$urlPath])) {
            $this->currentController = $this->routes[$urlPath][0];
            $this->currentMethod = $this->routes[$urlPath][1];
            $url = [];
        } else {
            // Map URL segments to controller
            if (isset($url[0])) {
                $controllerName = $this->resolveController($url[0]);
                $controllerFile = APP_ROOT . '/controllers/' . $controllerName . '.php';
                
                if (file_exists($controllerFile)) {
                    $this->currentController = $controllerName;
                    unset($url[0]);
                }
            }

            // Load controller
            require_once APP_ROOT . '/controllers/' . $this->currentController . '.php';
            $this->currentController = new $this->currentController;

            // Map URL segment to method
            if (isset($url[1])) {
                $methodName = $this->resolveMethod($url[1]);
                if (method_exists($this->currentController, $methodName)) {
                    $this->currentMethod = $methodName;
                    unset($url[1]);
                }
            }

            // Remaining URL segments become parameters
            $this->params = $url ? array_values($url) : [];

            // Call the controller method
            call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
            return;
        }

        // Load controller for route-matched URLs
        require_once APP_ROOT . '/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;
        
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    /**
     * Parse the URL from query string
     */
    private function parseUrl(): array {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }

    /**
     * Resolve controller name from URL segment
     */
    private function resolveController(string $segment): string {
        $map = [
            'auth' => 'AuthController',
            'dashboard' => 'DashboardController',
            'shipments' => 'ShipmentController',
            'disruptions' => 'DisruptionController',
            'fleet' => 'FleetController',
            'cold-chain' => 'ColdChainController',
            'coldchain' => 'ColdChainController',
            'copilot' => 'CopilotController',
            'recommendations' => 'RecommendationController',
            'reports' => 'ReportController',
            'profile' => 'ProfileController',
        ];

        return $map[strtolower($segment)] ?? 'DashboardController';
    }

    /**
     * Resolve method name from URL segment (convert kebab-case to camelCase)
     */
    private function resolveMethod(string $segment): string {
        $map = [
            'analyze-impact' => 'analyzeImpact',
            'risk-analysis' => 'riskAnalysis',
            'find-redeployment' => 'findRedeployment',
            'temperature-data' => 'getTemperatureData',
            'do-login' => 'doLogin',
        ];

        return $map[strtolower($segment)] ?? lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $segment))));
    }
}

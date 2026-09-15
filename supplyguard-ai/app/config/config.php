<?php
/**
 * SupplyGuard AI - Application Configuration
 * 
 * Central configuration file for the application.
 * All constants and settings are defined here.
 */

// Application
define('APP_NAME', 'SupplyGuard AI');
define('APP_TAGLINE', 'Detect. Predict. Recommend. Act.');
define('APP_VERSION', '1.0.0');

// URL Configuration
define('URL_ROOT', '/');
define('BASE_URL', 'http://localhost:8000/');

// Path Configuration
define('APP_ROOT', dirname(dirname(__FILE__)));
define('PUBLIC_ROOT', dirname(dirname(dirname(__FILE__))) . '/public');

// Session
define('SESSION_NAME', 'supplyguard_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// CSRF
define('CSRF_TOKEN_NAME', 'csrf_token');

// Pagination
define('ITEMS_PER_PAGE', 15);

// Risk Levels
define('RISK_LOW', 'LOW');
define('RISK_MEDIUM', 'MEDIUM');
define('RISK_HIGH', 'HIGH');
define('RISK_CRITICAL', 'CRITICAL');

// Risk Score Thresholds
define('RISK_THRESHOLD_LOW', 25);
define('RISK_THRESHOLD_MEDIUM', 50);
define('RISK_THRESHOLD_HIGH', 75);

// Cold Chain Thresholds
define('COLD_CHAIN_TEMP_MIN', 2.0);
define('COLD_CHAIN_TEMP_MAX', 8.0);
define('COLD_CHAIN_WARNING_MARGIN', 1.0);

// Cold Chain Severity Levels
define('SEVERITY_NORMAL', 'NORMAL');
define('SEVERITY_WARNING', 'WARNING');
define('SEVERITY_HIGH', 'HIGH');
define('SEVERITY_CRITICAL', 'CRITICAL');

// AI Provider Configuration
define('AI_PROVIDER', 'local'); // 'local' for deterministic engine, 'openai' for OpenAI, etc.
define('AI_API_KEY', ''); // Set via environment variable in production

// Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_OPS_MANAGER', 'ops_manager');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/../logs/error.log');

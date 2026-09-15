<?php
$currentPage = $_GET['url'] ?? '';
$currentPage = explode('/', $currentPage)[0] ?? 'dashboard';

// Get dynamic counts for badges
try {
    $db = Database::getInstance();
    $db->query("SELECT COUNT(*) as c FROM disruptions WHERE status = 'active'");
    $activeDisruptions = $db->single()->c ?? 0;
    
    $db->query("SELECT COUNT(*) as c FROM recommendations WHERE status = 'pending'");
    $pendingRecs = $db->single()->c ?? 0;
} catch (Exception $e) {
    $activeDisruptions = 0;
    $pendingRecs = 0;
}

$isAdmin = Auth::hasRole(ROLE_ADMIN);
?>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div>
            <h2>SupplyGuard</h2>
            <small>AI Copilot</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Main</div>
        
        <a href="<?= URL_ROOT ?>dashboard" class="nav-link <?= $currentPage === 'dashboard' || $currentPage === '' ? 'active' : '' ?>" id="nav-dashboard">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?= URL_ROOT ?>shipments" class="nav-link <?= $currentPage === 'shipments' ? 'active' : '' ?>" id="nav-shipments">
            <i class="fas fa-truck-fast"></i>
            <span>Shipments</span>
        </a>

        <a href="<?= URL_ROOT ?>disruptions" class="nav-link <?= $currentPage === 'disruptions' ? 'active' : '' ?>" id="nav-disruptions">
            <i class="fas fa-triangle-exclamation"></i>
            <span>Disruptions</span>
            <?php if ($activeDisruptions > 0): ?>
                <span class="nav-badge danger"><?= $activeDisruptions ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= URL_ROOT ?>fleet" class="nav-link <?= $currentPage === 'fleet' ? 'active' : '' ?>" id="nav-fleet">
            <i class="fas fa-truck-moving"></i>
            <span>Fleet</span>
        </a>

        <a href="<?= URL_ROOT ?>cold-chain" class="nav-link <?= $currentPage === 'cold-chain' ? 'active' : '' ?>" id="nav-cold-chain">
            <i class="fas fa-temperature-low"></i>
            <span>Cold Chain</span>
        </a>

        <div class="nav-section-title">Intelligence</div>

        <a href="<?= URL_ROOT ?>copilot" class="nav-link <?= $currentPage === 'copilot' ? 'active' : '' ?>" id="nav-copilot">
            <i class="fas fa-robot"></i>
            <span>AI Copilot</span>
        </a>

        <a href="<?= URL_ROOT ?>recommendations" class="nav-link <?= $currentPage === 'recommendations' ? 'active' : '' ?>" id="nav-recommendations">
            <i class="fas fa-lightbulb"></i>
            <span>Recommendations</span>
            <?php if ($pendingRecs > 0): ?>
                <span class="nav-badge warning"><?= $pendingRecs ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= URL_ROOT ?>reports" class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>" id="nav-reports">
            <i class="fas fa-file-lines"></i>
            <span>Reports</span>
        </a>

        <?php if ($isAdmin): ?>
        <div class="nav-section-title">System</div>

        <a href="<?= URL_ROOT ?>reports/activity" class="nav-link" id="nav-activity">
            <i class="fas fa-clock-rotate-left"></i>
            <span>Activity Log</span>
        </a>
        <?php endif; ?>

        <div style="padding: 20px; margin-top: 20px; border-top: 1px solid var(--border-color);">
            <a href="<?= URL_ROOT ?>logout" class="nav-link" style="padding-left: 0; border-left: none; color: var(--text-muted);">
                <i class="fas fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

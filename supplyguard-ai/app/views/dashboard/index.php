<?php
/** @var array $data */
$totalShipments = $data['totalShipments'];
$highRiskShipments = $data['highRiskShipments'];
$activeDisruptions = $data['activeDisruptions'];
$coldChainAlerts = $data['coldChainAlerts'];
$fleetCounts = $data['fleetCounts'];
$topRiskShipments = $data['topRiskShipments'];
$disruptions = $data['disruptions'];
$recentRecs = $data['recentRecs'];
$recentActivity = $data['recentActivity'];
$avgUtilization = $data['avgUtilization'];
$cargoValueAtRisk = $data['cargoValueAtRisk'];
$riskDistribution = $data['riskDistribution'];

$availableFleet = ($fleetCounts->available ?? 0) + ($fleetCounts->idle ?? 0);

// Prepare risk chart data
$riskData = ['LOW' => 0, 'MEDIUM' => 0, 'HIGH' => 0, 'CRITICAL' => 0];
foreach ($riskDistribution as $r) {
    $riskData[$r->risk_level] = (int)$r->count;
}

function formatCurrency($value) {
    if ($value >= 10000000) return '₹' . number_format($value / 10000000, 1) . ' Cr';
    if ($value >= 100000) return '₹' . number_format($value / 100000, 1) . ' L';
    if ($value >= 1000) return '₹' . number_format($value / 1000, 1) . 'K';
    return '₹' . number_format($value);
}

function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}
?>

<!-- AI Workflow Banner -->
<div class="ai-workflow">
    <div class="workflow-step"><i class="fas fa-radar"></i> DETECT</div>
    <i class="fas fa-chevron-right workflow-arrow"></i>
    <div class="workflow-step"><i class="fas fa-magnifying-glass-chart"></i> ANALYZE</div>
    <i class="fas fa-chevron-right workflow-arrow"></i>
    <div class="workflow-step"><i class="fas fa-chart-line"></i> PREDICT</div>
    <i class="fas fa-chevron-right workflow-arrow"></i>
    <div class="workflow-step"><i class="fas fa-lightbulb"></i> RECOMMEND</div>
    <i class="fas fa-chevron-right workflow-arrow"></i>
    <div class="workflow-step"><i class="fas fa-bolt"></i> ACT</div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-6">
        <div class="kpi-card primary">
            <div class="kpi-icon primary"><i class="fas fa-truck-fast"></i></div>
            <div class="kpi-value" id="kpi-total-shipments"><?= $totalShipments ?></div>
            <div class="kpi-label">Total Shipments</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="kpi-card danger">
            <div class="kpi-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-value" id="kpi-high-risk"><?= $highRiskShipments ?></div>
            <div class="kpi-label">High-Risk Shipments</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="kpi-card warning">
            <div class="kpi-icon warning"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="kpi-value" id="kpi-disruptions"><?= $activeDisruptions ?></div>
            <div class="kpi-label">Active Disruptions</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="kpi-card info">
            <div class="kpi-icon info"><i class="fas fa-temperature-low"></i></div>
            <div class="kpi-value" id="kpi-cold-chain"><?= $coldChainAlerts ?></div>
            <div class="kpi-label">Cold-Chain Alerts</div>
        </div>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="kpi-card success">
            <div class="kpi-icon success"><i class="fas fa-truck-moving"></i></div>
            <div class="kpi-value" id="kpi-fleet"><?= $availableFleet ?></div>
            <div class="kpi-label">Available Fleet</div>
        </div>
    </div>
</div>

<!-- Second Row: Cargo Value at Risk -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Cargo Value at Risk</div>
                <div style="font-size:28px;font-weight:800;color:var(--danger);"><?= formatCurrency($cargoValueAtRisk) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Fleet Utilization (In Transit)</div>
                <div style="font-size:28px;font-weight:800;color:var(--primary-light);"><?= $avgUtilization ?>%</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Fleet Status</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-top:4px;">
                    <span style="color:var(--success);">● <?= $fleetCounts->available ?? 0 ?> Available</span> &nbsp;
                    <span style="color:var(--info);">● <?= $fleetCounts->in_transit ?? 0 ?> Transit</span> &nbsp;
                    <span style="color:var(--text-muted);">● <?= $fleetCounts->idle ?? 0 ?> Idle</span> &nbsp;
                    <span style="color:var(--warning);">● <?= $fleetCounts->maintenance ?? 0 ?> Maint.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Shipment Risk Chart -->
    <div class="col-lg-4">
        <div class="card" style="height:100%;">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie me-2" style="color:var(--primary-light);"></i>Shipment Risk Overview</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="riskDoughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Disruptions -->
    <div class="col-lg-4">
        <div class="card" style="height:100%;">
            <div class="card-header">
                <h5><i class="fas fa-triangle-exclamation me-2" style="color:var(--warning);"></i>Active Disruptions</h5>
                <a href="<?= URL_ROOT ?>disruptions" class="btn-outline-custom btn-sm">View All</a>
            </div>
            <div class="card-body" style="padding:12px;">
                <?php if (empty($disruptions)): ?>
                    <div class="empty-state" style="padding:30px;">
                        <i class="fas fa-check-circle" style="color:var(--success);"></i>
                        <h4>No Active Disruptions</h4>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($disruptions, 0, 3) as $d): ?>
                    <div class="disruption-card mb-2" onclick="window.location='<?= URL_ROOT ?>disruptions/show/<?= $d->id ?>'">
                        <div class="disruption-header">
                            <div>
                                <h6 style="font-size:13px;font-weight:600;margin-bottom:4px;"><?= Validator::escape($d->title) ?></h6>
                                <div style="font-size:11px;color:var(--text-muted);">
                                    <i class="fas fa-location-dot me-1"></i><?= Validator::escape($d->location) ?>
                                </div>
                            </div>
                            <span class="badge-risk <?= strtolower($d->severity) ?>"><?= $d->severity ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Most Affected Shipments -->
    <div class="col-lg-4">
        <div class="card" style="height:100%;">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-circle me-2" style="color:var(--danger);"></i>Most At-Risk Shipments</h5>
                <a href="<?= URL_ROOT ?>shipments" class="btn-outline-custom btn-sm">View All</a>
            </div>
            <div class="card-body" style="padding:12px;">
                <?php foreach ($topRiskShipments as $s): ?>
                <a href="<?= URL_ROOT ?>shipments/show/<?= $s->id ?>" style="text-decoration:none;display:block;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-bottom:1px solid var(--border-color);">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary);"><?= Validator::escape($s->shipment_code) ?></div>
                            <div style="font-size:11px;color:var(--text-muted);"><?= Validator::escape($s->origin) ?> → <?= Validator::escape($s->destination) ?></div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge-risk <?= strtolower($s->risk_level) ?>"><?= $s->risk_level ?></span>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;"><?= $s->risk_score ?>/100</div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <!-- Fleet Utilization Chart -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-truck me-2" style="color:var(--accent);"></i>Fleet Utilization</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="fleetBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Recommendations -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-lightbulb me-2" style="color:var(--warning);"></i>AI Recommendations</h5>
                <a href="<?= URL_ROOT ?>recommendations" class="btn-outline-custom btn-sm">View All</a>
            </div>
            <div class="card-body" style="padding:12px;">
                <?php foreach (array_slice($recentRecs, 0, 4) as $rec): ?>
                <div class="rec-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <span class="badge-risk <?= strtolower($rec->priority) ?>"><?= strtoupper($rec->priority) ?></span>
                        <span style="font-size:11px;color:var(--text-muted);"><?= Validator::escape($rec->shipment_code ?? 'System') ?></span>
                    </div>
                    <div class="rec-text"><?= Validator::escape(substr($rec->recommendation_text, 0, 120)) ?>...</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Activity Log -->
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clock-rotate-left me-2" style="color:var(--text-muted);"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                <ul class="activity-timeline">
                    <?php foreach ($recentActivity as $act): ?>
                    <li class="activity-item">
                        <div class="activity-icon <?= $act->action ?>">
                            <i class="fas fa-<?= match($act->action) { 'login' => 'right-to-bracket', 'logout' => 'right-from-bracket', 'view' => 'eye', 'create' => 'plus', 'update' => 'pen', 'delete' => 'trash', 'analyze' => 'magnifying-glass-chart', 'query' => 'robot', default => 'circle' } ?>"></i>
                        </div>
                        <div class="activity-text">
                            <div class="desc">
                                <strong><?= Validator::escape($act->user_name ?? 'System') ?></strong> — 
                                <?= Validator::escape($act->description) ?>
                            </div>
                            <div class="time"><?= timeAgo($act->created_at) ?> · <?= Validator::escape($act->module) ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for charts -->
<script>
    window.riskChartData = <?= json_encode($riskData) ?>;
    window.fleetData = {
        available: <?= $fleetCounts->available ?? 0 ?>,
        in_transit: <?= $fleetCounts->in_transit ?? 0 ?>,
        idle: <?= $fleetCounts->idle ?? 0 ?>,
        maintenance: <?= $fleetCounts->maintenance ?? 0 ?>,
        reserved: <?= $fleetCounts->reserved ?? 0 ?>
    };
</script>

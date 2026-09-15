<?php /** @var array $data */ $fleet = $data['fleet']; $counts = $data['counts']; ?>
<div class="page-header">
    <div><h2><i class="fas fa-truck-moving me-2" style="color:var(--accent)"></i>Fleet Management</h2></div>
    <a href="<?= URL_ROOT ?>fleet/find-redeployment" class="btn-primary-custom btn-sm"><i class="fas fa-magnifying-glass-location"></i> Find Redeployment</a>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col"><div class="kpi-card primary"><div class="kpi-value"><?= $counts->total ?></div><div class="kpi-label">Total Vehicles</div></div></div>
    <div class="col"><div class="kpi-card success"><div class="kpi-value"><?= $counts->available ?></div><div class="kpi-label">Available</div></div></div>
    <div class="col"><div class="kpi-card info"><div class="kpi-value"><?= $counts->in_transit ?></div><div class="kpi-label">In Transit</div></div></div>
    <div class="col"><div class="kpi-card warning"><div class="kpi-value"><?= $counts->maintenance ?></div><div class="kpi-label">Maintenance</div></div></div>
    <div class="col"><div class="kpi-card" style="border-top:3px solid var(--text-muted);"><div class="kpi-value"><?= $counts->idle ?></div><div class="kpi-label">Idle</div></div></div>
</div>

<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>Vehicle</th><th>Type</th><th>Carrier</th><th>Location</th><th>Capacity</th><th>Status</th><th>Utilization</th><th>Temp</th><th>Fuel</th></tr></thead>
            <tbody>
            <?php foreach ($fleet as $v): ?>
            <tr>
                <td style="font-weight:600;color:var(--primary-light);"><?= Validator::escape($v->vehicle_code) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $v->vehicle_type)) ?></td>
                <td style="font-size:12px;"><?= Validator::escape($v->carrier) ?></td>
                <td style="font-size:12px;"><?= Validator::escape($v->current_location) ?></td>
                <td><?= $v->capacity_tons ?>T</td>
                <td><span class="badge-status <?= $v->status ?>"><?= ucfirst(str_replace('_', ' ', $v->status)) ?></span></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="utilization-bar" style="width:80px;">
                            <div class="utilization-fill <?= $v->utilization_percentage > 80 ? 'high' : ($v->utilization_percentage > 50 ? 'medium' : 'low') ?>" style="width:<?= $v->utilization_percentage ?>%"></div>
                        </div>
                        <span style="font-size:12px;"><?= $v->utilization_percentage ?>%</span>
                    </div>
                </td>
                <td><?= $v->temperature_capable ? '<i class="fas fa-snowflake" style="color:var(--accent);"></i> Yes' : '<span style="color:var(--text-muted);">No</span>' ?></td>
                <td><span style="color:<?= $v->fuel_level < 30 ? 'var(--danger)' : 'var(--success)' ?>"><?= $v->fuel_level ?>%</span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

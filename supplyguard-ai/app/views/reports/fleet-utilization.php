<?php /** @var array $data */ $fleet = $data['fleet']; $counts = $data['counts']; ?>
<div class="page-header"><h2>Fleet Utilization Report</h2><div><button onclick="window.print()" class="btn-outline-custom btn-sm"><i class="fas fa-print"></i> Print</button> <a href="<?= URL_ROOT ?>reports" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div></div>
<div class="row g-3 mb-4">
    <div class="col"><div class="kpi-card"><div class="kpi-value"><?= $counts->total ?></div><div class="kpi-label">Total</div></div></div>
    <div class="col"><div class="kpi-card success"><div class="kpi-value"><?= $counts->available ?></div><div class="kpi-label">Available</div></div></div>
    <div class="col"><div class="kpi-card info"><div class="kpi-value"><?= $counts->in_transit ?></div><div class="kpi-label">In Transit</div></div></div>
    <div class="col"><div class="kpi-card"><div class="kpi-value"><?= $counts->idle ?></div><div class="kpi-label">Idle</div></div></div>
    <div class="col"><div class="kpi-card warning"><div class="kpi-value"><?= $counts->maintenance ?></div><div class="kpi-label">Maintenance</div></div></div>
</div>
<div class="card"><div class="table-container">
    <table class="data-table"><thead><tr><th>Vehicle</th><th>Type</th><th>Carrier</th><th>Location</th><th>Capacity</th><th>Status</th><th>Utilization</th><th>Temp Capable</th><th>Fuel</th></tr></thead>
    <tbody>
    <?php foreach ($fleet as $v): ?>
    <tr>
        <td style="font-weight:600;"><?= Validator::escape($v->vehicle_code) ?></td>
        <td><?= ucfirst(str_replace('_', ' ', $v->vehicle_type)) ?></td>
        <td style="font-size:12px;"><?= Validator::escape($v->carrier) ?></td>
        <td style="font-size:12px;"><?= Validator::escape($v->current_location) ?></td>
        <td><?= $v->capacity_tons ?>T</td>
        <td><span class="badge-status <?= $v->status ?>"><?= ucfirst(str_replace('_', ' ', $v->status)) ?></span></td>
        <td><?= $v->utilization_percentage ?>%</td>
        <td><?= $v->temperature_capable ? 'Yes' : 'No' ?></td>
        <td><?= $v->fuel_level ?>%</td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div></div>

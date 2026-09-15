<?php /** @var array $data */ $shipments = $data['shipments']; ?>
<div class="page-header"><h2>Cold Chain Alert Report</h2><div><button onclick="window.print()" class="btn-outline-custom btn-sm"><i class="fas fa-print"></i> Print</button> <a href="<?= URL_ROOT ?>reports" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div></div>
<div class="card"><div class="table-container">
    <table class="data-table"><thead><tr><th>Shipment</th><th>Cargo</th><th>Route</th><th>Temp (°C)</th><th>Range (°C)</th><th>Severity</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($shipments as $s): $severityColors = ['NORMAL' => 'low', 'WARNING' => 'medium', 'HIGH' => 'high', 'CRITICAL' => 'critical']; ?>
    <tr>
        <td style="font-weight:600;"><a href="<?= URL_ROOT ?>cold-chain/show/<?= $s->id ?>" style="color:var(--primary-light);"><?= Validator::escape($s->shipment_code) ?></a></td>
        <td style="font-size:12px;"><?= ucfirst(str_replace('_', ' ', $s->cargo_type)) ?></td>
        <td style="font-size:12px;"><?= Validator::escape($s->origin) ?> → <?= Validator::escape($s->destination) ?></td>
        <td style="font-weight:700;color:<?= ($s->latest_temp > $s->temp_max || $s->latest_temp < $s->temp_min) ? 'var(--danger)' : 'var(--success)' ?>;"><?= $s->latest_temp ?? 'N/A' ?>°C</td>
        <td><?= $s->temp_min ?>°C – <?= $s->temp_max ?>°C</td>
        <td><span class="badge-risk <?= $severityColors[$s->severity] ?? 'low' ?>"><?= $s->severity ?></span></td>
        <td><span class="badge-status <?= $s->status ?>"><?= ucfirst(str_replace('_', ' ', $s->status)) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div></div>

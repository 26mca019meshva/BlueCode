<?php /** @var array $data */ $shipments = $data['shipments']; $alertCount = $data['alertCount']; $normalCount = $data['normalCount']; ?>
<div class="page-header">
    <div><h2><i class="fas fa-temperature-low me-2" style="color:var(--accent)"></i>Cold Chain Monitor</h2>
        <div class="breadcrumb">Real-time IoT sensor monitoring for temperature-sensitive cargo</div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="kpi-card primary"><div class="kpi-value"><?= count($shipments) ?></div><div class="kpi-label">Cold-Chain Shipments</div></div></div>
    <div class="col-md-4"><div class="kpi-card danger"><div class="kpi-value"><?= $alertCount ?></div><div class="kpi-label">Temperature Alerts</div></div></div>
    <div class="col-md-4"><div class="kpi-card success"><div class="kpi-value"><?= $normalCount ?></div><div class="kpi-label">Normal</div></div></div>
</div>

<div class="row g-3">
<?php foreach ($shipments as $s): 
    $severityColors = ['NORMAL' => 'success', 'WARNING' => 'warning', 'HIGH' => 'high', 'CRITICAL' => 'critical'];
    $color = $severityColors[$s->severity] ?? 'medium';
    $tempClass = ($s->latest_temp > $s->temp_max || $s->latest_temp < $s->temp_min) ? 'danger' : 'normal';
?>
<div class="col-lg-6">
    <div class="card" style="cursor:pointer;" onclick="window.location='<?= URL_ROOT ?>cold-chain/show/<?= $s->id ?>'">
        <div class="card-header">
            <div>
                <h5 style="font-size:15px;"><?= Validator::escape($s->shipment_code) ?> <?php if ($s->severity !== 'NORMAL'): ?><i class="fas fa-exclamation-triangle ms-1" style="color:var(--danger);font-size:12px;"></i><?php endif; ?></h5>
                <div style="font-size:11px;color:var(--text-muted);"><?= Validator::escape($s->origin) ?> → <?= Validator::escape($s->destination) ?> | <?= ucfirst(str_replace('_', ' ', $s->cargo_type)) ?></div>
            </div>
            <span class="badge-risk <?= $color ?>"><?= $s->severity ?></span>
        </div>
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div class="temp-display">
                        <span class="temp-value <?= $tempClass ?>"><?= $s->latest_temp !== null ? $s->latest_temp . '°C' : 'N/A' ?></span>
                    </div>
                    <div class="temp-range">Required: <?= $s->temp_min ?>°C – <?= $s->temp_max ?>°C</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px;color:var(--text-muted);">Humidity: <?= $s->latest_humidity ?? 'N/A' ?>%</div>
                    <div style="font-size:11px;color:var(--text-muted);">Last reading: <?= $s->last_reading_at ? date('H:i', strtotime($s->last_reading_at)) : 'N/A' ?></div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;"><span class="badge-status <?= $s->status ?>"><?= ucfirst(str_replace('_', ' ', $s->status)) ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

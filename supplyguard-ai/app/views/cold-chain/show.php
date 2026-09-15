<?php /** @var array $data */ $s = $data['shipment']; $readings = $data['readings']; $latest = $data['latest']; $severity = $data['severity']; $recommendation = $data['recommendation']; $recommendations = $data['recommendations']; $severityColors = ['NORMAL' => 'success', 'WARNING' => 'warning', 'HIGH' => 'high', 'CRITICAL' => 'critical']; $color = $severityColors[$severity] ?? 'medium'; ?>
<div class="page-header">
    <div><h2><i class="fas fa-temperature-low me-2" style="color:var(--accent)"></i><?= Validator::escape($s->shipment_code) ?> — Cold Chain</h2>
        <div class="breadcrumb">Dashboard / Cold Chain / <?= Validator::escape($s->shipment_code) ?></div></div>
    <a href="<?= URL_ROOT ?>cold-chain" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="row g-3">
    <!-- Status Card -->
    <div class="col-lg-4">
        <div class="card" style="height:100%;">
            <div class="card-body text-center">
                <div style="font-size:56px;font-weight:800;color:var(--risk-<?= $color ?>);">
                    <?= $latest ? $latest->temperature . '°C' : 'N/A' ?>
                </div>
                <div style="font-size:14px;color:var(--text-muted);margin-bottom:12px;">Current Temperature</div>
                <span class="badge-risk <?= $color ?>" style="font-size:14px;padding:8px 24px;"><?= $severity ?></span>
                <div style="margin-top:16px;padding:12px;background:rgba(51,65,85,0.3);border-radius:8px;">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Required Range</div>
                    <div style="font-size:18px;font-weight:700;color:var(--text-primary);"><?= $s->temp_min ?>°C – <?= $s->temp_max ?>°C</div>
                </div>
                <?php if ($latest): ?>
                <div style="margin-top:12px;font-size:12px;color:var(--text-muted);">
                    Humidity: <?= $latest->humidity ?>% | Last: <?= date('H:i', strtotime($latest->recorded_at)) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Details & Recommendation -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h5>Shipment Details</h5></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="label">Route</div><div class="value"><?= Validator::escape($s->origin) ?> → <?= Validator::escape($s->destination) ?></div></div>
                    <div class="detail-item"><div class="label">Current Location</div><div class="value"><?= Validator::escape($s->current_location) ?></div></div>
                    <div class="detail-item"><div class="label">Cargo</div><div class="value"><?= Validator::escape($s->cargo_description ?: ucfirst(str_replace('_', ' ', $s->cargo_type))) ?></div></div>
                    <div class="detail-item"><div class="label">Value</div><div class="value">₹<?= number_format($s->cargo_value) ?></div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5><i class="fas fa-robot me-2" style="color:var(--primary-light);"></i>AI Recommendation</h5></div>
            <div class="card-body">
                <div style="padding:14px;background:rgba(<?= $severity === 'CRITICAL' || $severity === 'HIGH' ? '239,68,68' : ($severity === 'WARNING' ? '245,158,11' : '16,185,129') ?>,0.08);border-left:4px solid var(--risk-<?= $color ?>);border-radius:0 8px 8px 0;font-size:13px;color:var(--text-secondary);line-height:1.6;">
                    <?= Validator::escape($recommendation) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Temperature Chart -->
<?php if (!empty($readings)): ?>
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h5><i class="fas fa-chart-line me-2" style="color:var(--primary-light);"></i>Temperature History</h5></div>
            <div class="card-body">
                <div class="chart-container"><canvas id="temperatureChart"></canvas></div>
            </div>
        </div>
    </div>
</div>
<script>
window.temperatureData = <?= json_encode(array_map(fn($r) => ['time' => date('H:i', strtotime($r->recorded_at)), 'temp' => (float)$r->temperature], $readings)) ?>;
window.tempRange = { min: <?= (float)$s->temp_min ?>, max: <?= (float)$s->temp_max ?> };
</script>
<?php endif; ?>

<!-- Existing Recommendations -->
<?php if (!empty($recommendations)): ?>
<div class="row g-3 mt-1"><div class="col-12"><div class="card">
    <div class="card-header"><h5>All Recommendations</h5></div>
    <div class="card-body">
        <?php foreach ($recommendations as $rec): ?>
        <div class="rec-card">
            <span class="badge-risk <?= strtolower($rec->priority) ?>"><?= strtoupper($rec->priority) ?></span>
            <div class="rec-text mt-2"><?= Validator::escape($rec->recommendation_text) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div></div></div>
<?php endif; ?>

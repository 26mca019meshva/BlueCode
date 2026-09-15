<?php /** @var array $data */ $redeployments = $data['redeployments']; ?>
<div class="page-header">
    <div><h2><i class="fas fa-magnifying-glass-location me-2" style="color:var(--primary-light)"></i>Fleet Redeployment</h2>
        <div class="breadcrumb">AI-powered matching of idle vehicles to high-risk shipments</div></div>
    <a href="<?= URL_ROOT ?>fleet" class="btn-outline-custom btn-sm"><i class="fas fa-arrow-left"></i> Back to Fleet</a>
</div>

<?php if (empty($redeployments)): ?>
<div class="card"><div class="empty-state"><i class="fas fa-check-circle" style="color:var(--success)"></i><h4>No redeployment needed</h4><p>All idle vehicles are either unsuitable or too far from high-risk shipments.</p></div></div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($redeployments as $r): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 style="font-size:14px;"><span style="color:var(--accent);"><?= $r['vehicle']['code'] ?></span> → <span style="color:var(--primary-light);"><?= $r['shipment']['code'] ?></span></h5>
                <span class="badge-risk <?= $r['suitability_score'] > 75 ? 'high' : ($r['suitability_score'] > 50 ? 'medium' : 'low') ?>">Score: <?= $r['suitability_score'] ?>/100</span>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;">Vehicle</div>
                        <div style="font-size:13px;font-weight:600;"><?= $r['vehicle']['code'] ?> (<?= ucfirst(str_replace('_', ' ', $r['vehicle']['type'])) ?>)</div>
                        <div style="font-size:11px;color:var(--text-muted);">📍 <?= $r['vehicle']['location'] ?> | <?= $r['vehicle']['temperature_capable'] ? '❄️ Temp Capable' : '' ?></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;">Shipment</div>
                        <div style="font-size:13px;font-weight:600;"><?= $r['shipment']['code'] ?> <span class="badge-risk <?= strtolower($r['shipment']['risk_level']) ?>"><?= $r['shipment']['risk_level'] ?></span></div>
                        <div style="font-size:11px;color:var(--text-muted);">📍 <?= $r['shipment']['current_location'] ?> | <?= ucfirst($r['shipment']['cargo_type']) ?></div>
                    </div>
                </div>
                <div style="font-size:12px;color:var(--text-secondary);padding:10px;background:rgba(79,70,229,0.05);border-radius:8px;border-left:3px solid var(--primary);">
                    <i class="fas fa-robot me-1" style="color:var(--primary-light);"></i> <?= Validator::escape($r['recommendation']) ?>
                </div>
                <div style="margin-top:8px;font-size:12px;color:var(--text-muted);">
                    📏 Distance: <strong><?= $r['estimated_distance_km'] ?> km</strong>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
